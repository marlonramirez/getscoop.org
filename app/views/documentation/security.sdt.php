<p>El módulo de <b>Security</b> de Scoop proporciona herramientas criptográficas y middlewares de protección para construir aplicaciones web seguras. Desde encriptación versionada hasta protección CSRF y CORS, el módulo está diseñado siguiendo los estándares modernos de seguridad web.</p>

<p><ul>
    <li><a href="#cipher">Sistema de Encriptación (Cipher)</a></li>
    <li><a href="#csrf">Protección CSRF</a></li>
    <li><a href="#cors">Control CORS</a></li>
    <li><a href="#good-parts">Buenas Prácticas de Seguridad</a></li>
</ul></p>

<h2>
    <a href="#cipher">Sistema de Encriptación (Cipher)</a>
    <span class="anchor" id="cipher">...</span>
</h2>

<p>La clase <code>Cipher</code> implementa un sistema de <b>encriptación versionada</b>, siendo el primer framework PHP con esta característica. Esto permite migrar algoritmos de encriptación de manera transparente sin necesidad de re-encriptar la base de datos completa.</p>

<pre><code class="language-php">class MyService
{
    public function __construct(private \Scoop\Security\Cipher $cipher)
    {
        $this->cipher = $cipher;
    }

    public function storeSensitiveData(string $data): void
    {
        $encrypted = $this->cipher->encrypt($data);
        return $this->cipher->decrypt($encrypted);
    }
}
</code></pre>

<h3>Configuración</h3>

<p>La clave de encriptación se configura en el archivo de configuración principal:</p>

<pre><code class="language-php">[
    'cipher' => [
        'secret' => 'your-secret-key-here-min-32-chars',
        'encoding' => 'base64'
    ]
]
</code></pre>

<p class="doc-alert"><b>Seguridad de la Clave:</b> La clave debe tener al menos 32 caracteres y ser criptográficamente segura. Use <code>openssl_random_pseudo_bytes(32)</code> o un generador de contraseñas confiable. <b>NUNCA</b> versione la clave en control de código fuente.</p>

<h3>Derivación de Clave (PBKDF2)</h3>

<p>Cipher utiliza algoritmos de derivación robustos (incluyendo PBKDF2 en entornos modernos), añadiendo una capa adicional de seguridad contra ataques de fuerza bruta:</p>

<h3>Migración de Algoritmos</h3>

<p>El sistema versionado permite migrar algoritmos sin downtime:</p>

<pre><code class="language-php">$oldData = "$0:abc123...";
$decrypted = $cipher->decrypt($oldData);
$newData = $cipher->encrypt($decrypted);
$db->update(['field' => $newData])->restrict('[id] = :id')->run(['id' => 1]);
</code></pre>

<h3>Encodings Soportados</h3>

<ul>
    <li><b>base64</b> (default): Ideal para almacenamiento en texto (DB VARCHAR, JSON)</li>
    <li><b>hex</b>: Formato hexadecimal, más verboso pero sin caracteres especiales</li>
    <li><b>raw</b>: Bytes crudos, solo para almacenamiento binario (BLOB)</li>
</ul>

<h2>
    <a href="#csrf">Protección CSRF</a>
    <span class="anchor" id="csrf">...</span>
</h2>

<p>El middleware <b>CsrfGuard</b> protege contra ataques de Cross-Site Request Forgery mediante tokens criptográficamente seguros y validación timing-safe.</p>

<h3>Configuración</h3>

<pre><code class="language-php">[
    'middlewares' => [
        '\Scoop\Security\Middleware\CsrfGuard'
    ]
]
</code></pre>

<h3>Uso en Templates</h3>

<p>Scoop proporciona la directiva <code>@csrf</code> que automáticamente inyecta el token CSRF en el lugar correcto:</p>

<pre><code class="language-html">&lt;head&gt;
    &#64;csrf
    &lt;title&gt;{{#view->getConfig('app.name')}}&lt;/title&gt;
&lt;/head&gt;

&lt;form method="POST" action="/users"&gt;
    &#64;csrf
    &lt;input type="text" name="name"&gt;
    &lt;button type="submit"&gt;Enviar&lt;/button&gt;
&lt;/form&gt;
</code></pre>

<h3>AJAX y APIs</h3>

<p>Para peticiones AJAX, el token puede enviarse en el header:</p>

<pre><code class="language-javascript">const token = document.querySelector('meta[name="csrf-token"]').content;
fetch('/api/users', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': token
    },
    body: JSON.stringify({name: 'John'})
});
</code></pre>

<h3>Validación</h3>

<p>CsrfGuard valida el token en este orden de prioridad:</p>

<ol>
    <li>Header <code>X-CSRF-Token</code></li>
    <li>Query parameter <code>?csrf-token=...</code></li>
    <li>Body field <code>csrf-token</code></li>
</ol>

<p class="alert">Para formularios con <code>enctype="multipart/form-data"</code> que manejen archivos de gran tamaño, se recomienda enviar el token via <code>query param</code> en el action o via header <code>X-CSRF-Token</code> al usar fetch, para evitar el costo de parsear el body completo en la validación CSRF.</p>

<p><b>Métodos HTTP exentos:</b> GET, HEAD, OPTIONS, TRACE no requieren token CSRF.</p>

<h3>Generación de Tokens</h3>

<p>Los tokens CSRF se generan con <code>openssl_random_pseudo_bytes()</code> (256 bits) en PHP 7.0+ o un fallback robusto con múltiples fuentes de entropía en PHP 5.4-5.6:</p>

<h3>Validación Timing-Safe</h3>

<p>CsrfGuard usa <code>hash_equals()</code> (PHP 5.6+) o un polyfill para prevenir ataques de timing:</p>

<h2>
    <a href="#cors">Control CORS</a>
    <span class="anchor" id="cors">...</span>
</h2>

<p>El middleware <b>CorsGuard</b> maneja Cross-Origin Resource Sharing para APIs REST, con soporte completo para preflight requests y wildcards de subdominios.</p>

<h3>Configuración</h3>

<p>Se debe incluir el middleware en cualquier capa de routing system.</p>

<pre><code class="language-php">'middlewares' => [
    '\Scoop\Security\Middleware\CorsGuard',
]</code></pre>

<p>Se puede configurar el middleware enviandole mediante factory o cualquier otro mecanismo un array de configuración.</p>

<pre><code class="language-php">[
    'origins' => 'https://example.com, https://*.example.com, http://localhost:3000',
    'methods' => 'GET, POST, PUT, PATCH, DELETE',
    'headers' => 'Content-Type, Authorization, X-Requested-With',
    'expose-headers' => 'X-Content-Type-Options, X-Frame-Options, Referrer-Policy',
    'credentials' => true,
    'max-age' => 86400
]
</code></pre>

<h3>Wildcard de Subdominios</h3>

<p>CorsGuard soporta wildcards para permitir todos los subdominios de un dominio:</p>

<pre><code class="language-php">'origins' => [
    'https://*.example.com'
]
</code></pre>

<p>Si no se configura el middleware de CORS pero se incluye en la pila, se aceptara cualquier petición hecha a los endpoints.</p>

<p class="doc-alert"><b>Importante:</b> Nunca use <code>'*'</code> (wildcard total) en origins si <code>credentials</code> está habilitado. Los navegadores modernos bloquean esta combinación por seguridad. Use una lista explícita de dominios o wildcards de subdominios específicos.</p>

<h3>Preflight Requests</h3>

<p>CorsGuard maneja automáticamente las peticiones OPTIONS para CORS preflight:</p>

<pre><code class="language-bash">OPTIONS /api/users HTTP/1.1
Host: api.example.com
Origin: https://app.example.com
Access-Control-Request-Method: POST
Access-Control-Request-Headers: Content-Type

HTTP/1.1 204 No Content
Access-Control-Allow-Origin: https://app.example.com
Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE
Access-Control-Allow-Headers: Content-Type, Authorization
Access-Control-Expose-Headers: X-Content-Type-Options, X-Frame-Options, Referrer-Policy
Access-Control-Max-Age: 86400
</code></pre>

<h3>Credenciales (Cookies)</h3>

<p>Para permitir el envío de cookies en peticiones cross-origin:</p>

<pre><code class="language-php">'cors' => [
    'credentials' => true
]
</code></pre>

<p>En el frontend debe incluir credentials.</p>

<pre><code class="language-javascript">
fetch('https://api.example.com/users', {
    credentials: 'include'
});
</code></pre>

<h3>Headers Personalizados</h3>

<p>Para permitir headers personalizados en las peticiones:</p>

<pre><code class="language-php">[
    'headers' => 'Content, Authorization, X-API-Key, X-Request-ID'
]
</code></pre>

<h2>
    <a href="#good-parts">Buenas Prácticas de Seguridad</a>
    <span class="anchor" id="good-parts">...</span>
</h2>

<h3>Endurecimiento de Respuesta (Hardening)</h3>

<p>Fiel a la filosofía de soberanía de Scoop, el core no inyecta cabeceras de seguridad de forma intrusiva. En su lugar, proporciona la infraestructura necesaria para que se defina su propia política de "Hardening" mediante middlewares.</p>

<p>Recomendamos crear un middleware de infraestructura para blindar el comportamiento del navegador del usuario final:</p>

<pre><code class="language-php">class SecurityHeadersMiddleware
{
    public function process($request, $next)
    {
        return $next->handle($request)
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('X-Frame-Options', 'SAMEORIGIN')
            ->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->withHeader('Content-Security-Policy', "default-src 'self'");
    }
}
</code></pre>

<p>Al registrar este middleware en la raíz de <code>app/routes/middlewares.php</code>, toda la aplicación quedará protegida contra ataques de Clickjacking, MIME-sniffing y fugas de procedencia (Referrer).</p>

<p class="doc-alert"><b>¿Por qué no es automático?</b> Porque Scoop respeta tus casos de uso. Si una sección de tu aplicación necesita ser cargada en un iframe externo o requiere una política CSP relajada, tienes la libertad total de configurar o excluir este middleware en esa rama del ruteo.</p>

<h3>Almacenamiento de Claves</h3>

<p>Lo ideal es usar variables de entorno para no introducir secretos directamente en la configuración del sistema.</p>

<pre><code class="language-bash">export CIPHER_KEY="$(openssl rand -base64 32)"
CIPHER_KEY=your-secret-key-here
</code></pre>

<p>Luego se debe traer en la configuración principal.</p>

<pre><code class="language-php">[
    'cipher' => [
        'secret' => getenv('CIPHER_KEY')
    ]
]
</code></pre>

<h3>Rotación de Claves</h3>

<p>Para rotar claves de encriptación:</p>

<ol>
    <li>Generar nueva clave</li>
    <li>Configurar ambas claves (old + new)</li>
    <li>Script de migración que desencripta con old y re-encripta con new</li>
    <li>Actualizar registros en background</li>
    <li>Remover clave antigua cuando migración completa</li>
</ol>

<h3>CSRF en SPAs</h3>

<p>Para Single Page Applications:</p>

<pre><code class="language-javascript">const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
axios.defaults.headers.common['X-CSRF-Token'] = csrfToken;
axios.post('/api/users', {name: 'John'});
</code></pre>

<h3>CORS en Microservicios</h3>

<p>En arquitecturas de microservicios, configure CORS solo en el API Gateway o BFF, no en cada servicio individual:</p>

<pre><code class="language-php">'cors' => [
    'origins' => 'https://app.example.com'
]
</code></pre>
