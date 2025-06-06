@if($environment === '')
    // $environment is injected by the Envoy CLI via the --environment flag.
    // If you omit the flag, it defaults to 'dev'.
    $environment = 'dev';
@endif

@setup
    $siteName = getenv('SITE_NAME') ?: 'screensuggest';

    switch($environment) {
        case 'dev':
            // Development environment settings
            $servers   = ['web' => 'forge@116.202.22.113'];
            $branch    = 'dev';
            $appEnv    = 'dev';
            $hostnameRoot = 'dev.anarzone.com';
            break;
        case 'prod':
            // Production environment settings
            $servers   = ['web' => 'forge@116.202.22.113'];
            $branch    = 'main';
            $appEnv    = 'prod';
            $hostnameRoot = 'prod.anarzone.com';
            break;
        default:
            throw new Exception("Unknown environment: {$environment}");
    }
    // Convert siteName to lowercase and remove any spaces for subdomain usage
    $siteSubdomain = strtolower(str_replace(' ', '', $siteName));

    // Create hostname using subdomain format instead of path
    $hostname = $siteSubdomain . '.' . $hostnameRoot;
    $baseUrl = 'https://' . $hostname;
    $app_dir = '/home/forge/' . $hostname;

    # add linked directories here without trailing or leading slashes
    $symlinks = [
        'var/log' => 'var/log',
        'var/sessions' => 'var/sessions',
    ];

// Repository and directory settings
    $repository = "https://github.com/anarzone/screenSuggest.git";
    $releases_dir = $app_dir . '/releases';
    $release = date('YmdHis');
    $new_release_dir = $releases_dir . '/' . $release;
    
    // Paths for PHP and Composer executables
    $phpPath = '/usr/bin/php8.3';
    $composerPath = '/usr/local/bin/composer';
    
    // Webhook for deployment badge updates (update with your real webhook URL)
    // $webhook = 'https://forge.laravel.com/servers/906819/sites/2681789/deploy/http?token=y6nrRCAxKkG3ze5wExSieRsdshKJFtxUPE28gsOG';
@endsetup

@servers($servers)

@story('deploy')
    clone_repository
    update_env
    run_composer
    decrypt_secrets
    run_migrations
    install_assets
    update_symlinks
    restart_messenger
    deployment_cleanup
@endstory

@story('rollback')
    deployment_rollback
@endstory

@before
    echo "Starting $task" . PHP_EOL;
@endbefore

@after
    echo "Finished $task" . PHP_EOL;
@endafter

############################### Tasks ###################################
@task('clone_repository')
    [ -d {{ $releases_dir }} ] || mkdir -p {{ $releases_dir }}
    git clone --depth 1 -b {{ $branch }} "{{ $repository }}" {{ $new_release_dir }}
@endtask

@task('update_env')
    echo 'APP_ENV="{{ $appEnv }}"' > {{ $new_release_dir }}/.env.local
    echo 'BASE_URL="{{ $baseUrl }}"' >> {{ $new_release_dir }}/.env.local
    echo 'APP_DEBUG=0' >> {{ $new_release_dir }}/.env.local
@endtask

@task('run_composer')
    cd {{ $new_release_dir }}
    export APP_ENV={{ $appEnv }}
    echo "Installing composer dependencies"
    {{ $phpPath }} {{ $composerPath }} install --prefer-dist --no-scripts -q -o
@endtask

@task('decrypt_secrets')
    export APP_ENV="{{ $appEnv }}"
    cd {{ $new_release_dir }}
    echo "Decrypting secrets"
    SYMFONY_DECRYPTION_SECRET="{{ $secret }}" {{ $phpPath }} bin/console secrets:decrypt-to-local -vvv --force --no-interaction --no-debug
@endtask

@task('run_migrations')
    echo "Checking for database updates"
    export APP_ENV={{ $appEnv }}
    cd {{ $new_release_dir }}
    {{ $phpPath }} bin/console doctrine:database:create --if-not-exists
    if {{ $phpPath }} bin/console doctrine:migrations:up-to-date; then
    echo "No pending migrations"
    else
    echo "Running migrations"
    {{ $phpPath }} bin/console doctrine:migrations:migrate --no-interaction
    fi
    {{ $phpPath }} bin/console messenger:setup-transports -q
@endtask

@task('install_assets')
    cd {{ $new_release_dir }}
    export APP_ENV={{ $appEnv }}
    echo "Installing assets"
    {{ $phpPath }} bin/console assets:install
@endtask

@task('update_symlinks')
    echo 'Updating symlinks...'
    
    @foreach ($symlinks as $target => $link)
        # remove existing directory in newly created release
        rm -rf {{ $new_release_dir }}/{{ $link }}
        echo "Creating symlink: {{ $link }} -> {{ $target }}"
        # create shared directory if it does not exist
        if [ ! -d {{ $app_dir }}/shared/{{ $target }} ]; then
            mkdir -p {{ $app_dir }}/shared/{{ $target }}
        fi
        ln -nfs {{ $app_dir }}/shared/{{ $target }} {{ $new_release_dir }}/{{ $link }}
    @endforeach

    echo 'Switching to new release...'
    ln -nfs {{ $new_release_dir }} {{ $app_dir }}/current
@endtask

@task('restart_messenger')
    echo "Restarting messenger"
    export APP_ENV={{ $appEnv }}
    cd {{ $app_dir }}/current
    {{ $phpPath }} {{ $app_dir }}/current/ bin/console messenger:stop-workers
@endtask

@task('deployment_cleanup')
    cd {{ $releases_dir }}
    find . -maxdepth 1 -name "20*" | sort | head -n -4 | xargs rm -Rf
    echo "Cleaned up old deployments"
@endtask

@task('dumpenv', ['on' => 'web'])
    cd {{ $app_dir }}/current
    {{ $phpPath }} bin/console debug:container --env-vars
@endtask

@task('deployment_rollback', ['on' => 'web', 'confirm' => true])
    cd {{ $releases_dir }}
    ln -nfs {{ $releases_dir }}/$(find . -maxdepth 1 -name "20*" | sort  | tail -n 2 | head -n1) {{ $app_dir }}/current
    echo "Rolled back to $(find . -maxdepth 1 -name '20*' | sort | tail -n 2 | head -n1)"
@endtask
