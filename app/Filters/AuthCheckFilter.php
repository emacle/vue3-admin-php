<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

use \Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use UnexpectedValueException;

class AuthCheckFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // 
        $allowedOrigins = ['http://localhost:9527', 'http://www.baidu.com'];

        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        if (in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        }
        header("Access-Control-Allow-Headers: X-API-KEY, Origin,X-Requested-With, Content-Type, Accept, Access-Control-Requested-Method, Authorization");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PATCH, PUT, DELETE");
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { // Routes.php 里必须有定义 $routes->options('blog', 'Blog::options');  $routes->resource('blog');默认不会创建options的路由方法
            // 如果返回 Response 实例,将向客户端发送响应,并停止脚本执行。这对于实现 API 的速率限制很有用。
            // die(); // return 200 空对象
            return $this->createErrorResponse(204, json_encode(null)); // 直接返回204空对象即可
        }

        $uri = $request->getUri()->getPath();  // string(21) "/apix/v2/sys/employee"
        $parts = explode("/", $uri);
        $pathToFind = "/" . implode("/", array_slice($parts, 3)) . "/" . strtolower($request->getMethod()); // "/sys/blog/get"

        list($Token) = sscanf($request->getHeaderLine('Authorization'), 'Bearer %s');
        if (is_null($Token)) {
            $message = [
                "code" => 50014,
                "message" => 'token is null'
            ];
            // 如果返回 Response 实例,将向客户端发送响应,并停止脚本执行。这对于实现 API 的速率限制很有用。
            return $this->createErrorResponse(401, json_encode($message)); // 未授权错误
        }

        try {
            $decoded = JWT::decode($Token, new Key('pocoyo', 'HS256'));
            $userId = $decoded->user_id;
            $appConfig = config('App'); // 获取app/Config/文件夹里变量，如config('Pager')
            $apiUrl = $appConfig->baseURL . 'api/v2/sys/menu/perms?userid=' . $userId;

            $client = curl_init($apiUrl);
            curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($client);
            curl_close($client);

            // 处理API的返回值
            $jsonArray = json_decode($response, true);
            $found = false;
            foreach ($jsonArray['data'] as $item) {
                if ($pathToFind === $item['path']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return $this->createErrorResponse(401, '用户无权限 ' . $pathToFind);
            }
        } catch (ExpiredException $e) { // access_token过期
            $message = [
                "code" => 50014,
                "message" => $e->getMessage()
            ];
            // var_dump($message);
            return $this->createErrorResponse(401, json_encode($message));
        } catch (UnexpectedValueException $e) {
            // provided JWT is malformed OR
            // provided JWT is missing an algorithm / using an unsupported algorithm OR
            // provided JWT algorithm does not match provided key OR
            // provided key ID in key/key-array is empty or invalid.
            $message = [
                "code" => 50015,
                "message" => $e->getMessage()
            ];
            // var_dump($message);
            return $this->createErrorResponse(401, json_encode($message));
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
        return "AuthCheckFilter after";
    }

    // filter 无法使用use ResponseTrait;构造简单的http响应函数
    private function createErrorResponse($statusCode, $message)
    {
        $response = service('response');
        $response->setStatusCode($statusCode);
        $response->setBody($message);
        return $response;
    }
}
