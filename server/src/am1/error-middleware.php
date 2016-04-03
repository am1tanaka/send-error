<?php
/**
 * エラー処理用のミドルウェア.
 *
 * @copyright 2016 YuTanak@AmuseOne
 */
namespace Am1;

/**
 * 登録する前の不正パラメータチェック.
 */
class EntryErrorMiddleWare
{
    public function __invoke($request, $response, $next)
    {
        $response->getBody()->write('before<br/>');
        $response->getBody()->write($_POST['description'].'<br/>');
        $route = $request->getAttribute('route');
        if ($app->util_error) {
            $response->getBody()->write('body ok<br/>');
        } else {
            $response->getBody()->write('body ng<br/>');
        }

        $response = $next($request, $response);
        $response->getBody()->write('after<br/>');

        return $response;
    }
}
