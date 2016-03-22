# send-error
JavaScriptのプログラムでエラーが発生した時に、エラー内容を管理者に報告するためのライブラリとサーバー。

# 動作環境
## サーバー
- SlimPHP
- PHPUnit
- Bootstrap
- Twig-View

## クライアント
- Gulp
- Uglify
- Jest

# 要件
## サーバー
- SlimPHPアプリを設置
- POST / で、登録情報を渡すとデータ登録。アクセスURLをメール送信
- POST /キー文字列/delete で、キー文字列の記事を削除
- GET /キー文字列で、キー文字列のエラー情報を表示

## クライアント
- JavaScriptようのライブラリを用意
- 決まった内容と、追加情報を、JSON形式でサーバーに送信する

