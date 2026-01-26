<p>Scoop está diseñado para escalar desde despliegues atómicos —basados en la transferencia simple de archivos— hasta flujos de trabajo avanzados con tuberías de <b>Integración y Despliegue Continuo (CI/CD)</b>. La arquitectura del motor garantiza que, independientemente del método elegido, el sistema mantenga su integridad y alto rendimiento.</p>

<ul>
<li><a href="#quality">Estándares de Calidad</a></li>
<li><a href="#automation">Automatización con Hooks</a></li>
<li><a href="#optimization">Optimización de Producción</a></li>
<li><a href="#cicd">Integración Continua (GitHub Actions)</a></li>
<li><a href="#manual">Estrategia de Despliegue Manual</a></li>
</ul>

<h2>
    <a href="#quality">Estándares de Calidad</a>
    <span class="anchor" id="quality">...</span>
</h2>

<p>Para asegurar que la aplicación cumpla con los estándares de la industria (como las normas PSR), Scoop integra soporte nativo para las herramientas líderes de análisis y pruebas del ecosistema PHP:</p>

<ul>
    <li><b>Pruebas (Unit & Integration):</b> Gracias al desacoplamiento del <b>Injector</b>, Scoop facilita el testeo unitario del Dominio (POPOs) y pruebas de integración mediante <code>PHPUnit</code>, permitiendo mockear adaptadores de infraestructura sin esfuerzo.</li>
    <li><b>Linter (Estilo):</b> Se utiliza <code>PHPCS</code> para garantizar que el código siga las normas de estilo definidas en <code>app/phpcs.xml</code>, manteniendo una base de código homogénea.</li>
    <li><b>Análisis Estático:</b> Para mitigar los riesgos de la flexibilidad del lenguaje, Scoop se apoya en <code>PHPStan</code>. Recomendamos niveles de análisis estrictos para validar la seguridad de tipos y la lógica de los grafos de inyección.</li>
</ul>

<h2>
    <a href="#automation">Automatización con Hooks</a>
    <span class="anchor" id="automation">...</span>
</h2>

<p>Para garantizar que ninguna "mala práctica" o error de sintaxis llegue al repositorio, Scoop recomienda el uso de <b>GrumPHP</b>. Esta herramienta actúa como un guardián en el entorno local, ejecutando la suite de calidad automáticamente antes de cada <i>commit</i>.</p>

<pre><code class="language-shell">composer require --dev phpro/grumphp-shim</code></pre>

<p>Configuración recomendada en <code>grumphp.yml</code>:</p>

<pre><code class="language-yaml">grumphp:
    process_timeout: null
    tasks:
        phpcs:
            standard: [app/phpcs.xml]
            whitelist_patterns:
                - /^src\/(.*)/
        phpstan:
            configuration: app/phpstan.neon
            ignore_patterns:
                - /^scoop\/(.*)/
        phpunit:
            config_file: app/phpunit.xml
</code></pre>

<h2>
    <a href="#optimization">Optimización de Producción</a>
    <span class="anchor" id="optimization">...</span>
</h2>

<p>A diferencia del entorno de desarrollo, donde Scoop prioriza la flexibilidad y el descubrimiento dinámico, en producción el motor debe operar en <b>Modo Inmutable</b>. El proceso de construcción (build) utiliza el CLI <code>ice</code> para eliminar la reflexión del <i>Hot Path</i>:</p>

<pre><code class="language-shell">php app/ice scan routes
php app/ice cache types
php app/ice preload json:package
</code></pre>

<p>Este proceso transforma la jerarquía de archivos y las definiciones dinámicas en mapas de PHP plano optimizados para <b>Opcache</b>, eliminando el coste de I/O y reflexión en cada petición. Se recomienda automatizar estos comandos en el <code>composer.json</code> bajo el evento <code>post-install-cmd</code> o mediante un comando de <code>build</code> dedicado.</p>

<h2>
    <a href="#cicd">Integración Continua (GitHub Actions)</a>
    <span class="anchor" id="cicd">...</span>
</h2>

<p>Scoop se integra de forma natural en flujos de trabajo modernos. A continuación, se presenta una configuración avanzada para <b>GitHub Actions</b> que ilustra un ciclo completo: pruebas, construcción de imagen Docker (AWS ECR) y despliegue automatizado.</p>

<pre><code class="language-yaml">name: CI/CD
on:
  pull_request:
    branches: master
  push:
    branches:
      - master
      - dev
concurrency:
  group: ci-$&#123;{ github.ref }&#125;
  cancel-in-progress: true
env:
  TAG: $&#123;{ github.sha }&#125;
  AWS_REGION: us-east-2
  ECR_REPOSITORY: hiring$&#123;{ github.ref_name == 'dev' && '-dev' || '' }&#125;
  ECR_REGISTRY: $&#123;{ secrets.AWS_ACCOUNT_ID }&#125;.dkr.ecr.us-east-2.amazonaws.com
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout code
        uses: actions/checkout@v4
        with:
          fetch-depth: 0
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
          extensions: sockets amqp
          coverage: none
      - name: Install Composer dependencies
        run: composer install --prefer-dist --no-interaction --no-progress --optimize-autoloader
      - name: Execute tests
        run: composer test
  build:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout code
        uses: actions/checkout@v4
        with:
          fetch-depth: 0
      - name: Configure AWS credentials
        uses: aws-actions/configure-aws-credentials@v4
        with:
          aws-access-key-id: $&#123;{ secrets.AWS_ACCESS_KEY_ID }&#125;
          aws-secret-access-key: $&#123;{ secrets.AWS_SECRET_ACCESS_KEY }&#125;
          aws-region: $&#123;{ env.AWS_REGION }&#125;
      - name: Login to Amazon ECR
        uses: aws-actions/amazon-ecr-login@v2
      - name: Set up Docker Buildx
        uses: docker/setup-buildx-action@v3
      - name: Build and export
        uses: docker/build-push-action@v6
        with:
          push: true
          provenance: false
          tags: $&#123;{ env.ECR_REGISTRY }&#125;/$&#123;{ env.ECR_REPOSITORY }&#125;:$&#123;{ env.TAG }&#125;
          cache-from: type=registry,ref=$&#123;{ env.ECR_REGISTRY }&#125;/$&#123;{ env.ECR_REPOSITORY }&#125;:cache
          cache-to: type=registry,ref=$&#123;{ env.ECR_REGISTRY }&#125;/$&#123;{ env.ECR_REPOSITORY }&#125;:cache,mode=max
  check:
    runs-on: ubuntu-latest
    if: github.event_name != 'pull_request'
    needs:
      - test
      - build
    steps:
      - name: All checks passed
        run: |
          echo ✅ Build and test successful.
          echo Image $&#123;{ env.ECR_REGISTRY }&#125;/$&#123;{ env.ECR_REPOSITORY }&#125;:$&#123;{ github.sha }&#125; is ready for deployment.
  deploy:
    runs-on: ubuntu-latest
    needs: check
    strategy:
      fail-fast: true
      matrix:
        include:
          - name: WEPS
            ip: 127.0.0.1
            branch: master
            protocol: tcp4
          - name: WEPS staging
            ip: 2a01:4f9:c013:fbdf::1
            branch: dev
            protocol: tcp6
    name: $&#123;{ matrix.name }&#125;
    steps:
      - name: Set up WARP
        uses: fscarmen/warp-on-actions@v1.3
        if: github.ref_name == matrix.branch && matrix.protocol == 'tcp6'
        with:
          stack: ipv6
          mode: client
      - name: Wait for IPv6 network readiness
        if: github.ref_name == matrix.branch && matrix.protocol == 'tcp6'
        run: |
          for i in {1..5}; do
            if ping -6 -c 1 $&#123;{ matrix.ip }&#125;; then
              echo "IPv6 network is ready."
              exit 0
            fi
            echo "Waiting for IPv6 network... (attempt $i)"
            sleep 5
          done
          echo "IPv6 network is not ready after several attempts."
          exit 1
      - name: deploy to server
        uses: appleboy/ssh-action@v1
        if: github.ref_name == matrix.branch
        with:
          host: $&#123;{ matrix.ip }&#125;
          username: deployer
          password: $&#123;{ secrets.DEPLOYER_PASSWORD }&#125;
          protocol: $&#123;{ matrix.protocol }&#125;
          envs: AWS_REGION,TAG,ECR_REGISTRY,ECR_REPOSITORY
          script: ./deploy.sh
</code></pre>

<p class="doc-alert"><b>Seguridad:</b> Nunca incluya secretos (llaves de Vault o contraseñas de DB) directamente en el archivo YAML. Utilice los <i>Secrets</i> de GitHub y mapee los valores mediante variables de entorno.</p>

<h2>
    <a href="#manual">Estrategia de Despliegue Manual</a>
    <span class="anchor" id="manual">...</span>
</h2>

<p>Si opta por un despliegue manual (FTP/SCP), es vital no transferir los archivos de desarrollo para reducir la superficie de ataque y mejorar el rendimiento. Un despliegue "limpio" de Scoop debe contener únicamente los artefactos de ejecución:</p>

<pre><code class="language-shell">├─ app
|   ├─ config
|   |    ├─ lang
|   |    |    ├─ en.php
|   |    |    └─ es.php
|   |    ├─ routes.php
|   |    └─ providers.php
|   ├─ storage
|   ├─ views
|   ├─ config.php
|   └─ ice
├─ public
|   ├─ css
|   ├─ fonts
|   ├─ images
|   ├─ js
|   ├─ favicon.ico
|   ├─ humans.txt
|   └─ robots.txt
├─ scoop
├─ src
├─ vendor
├─ .htaccess
├─ composer.json
├─ index.php
└─ package.json
</code></pre>

<p class="doc-alert"><b>Pro-Tip de Despliegue:</b> Asegúrese siempre de ejecutar <code>composer install --optimize-autoloader --no-dev</code> en el servidor de destino para minimizar la latencia del cargador de clases de PHP.</p>

<p>Para profundizar en la organización de los archivos, consulte la sección de <a href="{{#view->route('doc', 'application')}}#structure">Estructura de directorios</a>.</p>
