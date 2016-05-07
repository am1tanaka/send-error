# send-error
JavaScriptのプログラムでエラーが発生した時に、エラー内容を管理者に報告するためのライブラリとサーバープログラム。また、不正なアクセスの停止などのサービスも用意。

# sideci
- https://www.sideci.com/github_repositories/54460897/issues
- [株式会社インフィニットループ PSR-1 基本コーディング規約（日本語）](http://www.infiniteloop.co.jp/docs/psr/psr-1-basic-coding-standard.html)


# アクセス
- POST /error
  - $_POST[‘description’] JSONの文字列。DBに登録
- GET /error/{key}
  - 指定のキーのエラーを表示する
- GET /error/{key}/delete
  - 指定のキーのエラーを削除
- GET /invalid-access/{key}/release
  - 指定のキーの不正アクセスを削除
- GET /invalid-access/{key}/ng
  - 指定のキーの不正アクセすデータを、NGに登録する
- GET /invalid-access/ng/{key}/release
  - 指定のキーで登録されているNGのホストを解除する
- GET /test
  - テスト登録実行。デバッグ時のみ有効
- GET /app
  - エラー送信のテストページを表示。デバッグ時のみ有効


# 動作の流れ
## JavaScript側
1. JavaScriptでエラー発生
2. 必要事項を含めて、JSONのオブジェクト文字列にしてサーバーに送信

## サーバー側
1. POSTでデータを受付
2. 停止アカウント判定
2. 整合性チェック
3. 整合性がなければ停止登録して終わり
4. 整合性があればDBに登録
5. 登録したことをシステムにメール送信
6. キーを指定してGETすると、エラーの参照
7. 無効なキーを送ってきた場合は停止登録
7. POSTでキーを指定してdeleteをつけると、データの削除
8. 無効なキーを送ってきた場合は停止登録

# 不正なアクセスの判別
## アプリ側の機能
- 送りつけられた内容をそのまま関数に渡すと判定
  - 同一接続元から、同一の内容のデータが送られてきたら、回数を増やして本体には登録しない
  - 同一接続元から、一定時間以内に、一定の回数のアクセスがあったらメールで報告
- アプリ側のポリシーに反していたら、警告登録
- 停止中の接続元かを確認する関数

## 外部との連携
- メールからの操作で、指定の送信元を停止。再開のリンクを送信
- メールからの操作で、指定の送信元を再開。


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

