<?php

declare(strict_types=1);

namespace App\View\Helper;

use ViteHelper\View\Helper\ViteScriptsHelper as BaseViteScriptsHelper;

/**
 * ベンダー提供の ViteScriptsHelper をアプリ用に拡張したヘルパー。
 *
 * 機能概要:
 * - 短縮名（例: 'login_index'）を受け取り、
 *   'resources/js/login/index.tsx' などの実パスへ解決します
 *   （拡張子は tsx, ts, jsx, js の順で探索）。
 * - 配列で複数の短縮名を受け取る書き方にも対応
 *   （例: $this->ViteScripts->script(['login_index', 'dashboard_home'])）。
 * - 解決したパスをベンダー仕様のオプション（'files' / 'devEntries'）に詰め替えて、
 *   親クラスの機能へ委譲します。
 */
class ViteScriptsHelper extends BaseViteScriptsHelper
{
    /**
     * 短縮名を解決する際のベースディレクトリ（相対パス）。
     * 既定では 'resources/js' を指します。
     * @var string
     */
    protected string $baseDir = 'resources/js';

    /**
     * 短縮名解決で試行する拡張子の優先順。
     * @var string[]
     */
    protected array $extensions = ['tsx', 'ts', 'jsx', 'js'];

    /**
     * script() を拡張し、短縮名や配列形式の指定に対応します。
     * 正規化後は親クラス（ベンダー）の script() に委譲します。
     *
     * @param array|string $options 短縮名（文字列）または短縮名の配列、もしくはベンダー仕様のオプション配列
     * @param mixed $config ベンダーの設定キーまたは設定インスタンス
     */
    public function script(array|string $options = [], $config = null): void
    {
        $options = $this->normalizeOptions($options);
        parent::script($options, $config);
    }

    /**
     * css() を拡張し、短縮名や配列形式の指定に対応します。
     * 正規化後は親クラス（ベンダー）の css() に委譲します。
     *
     * @param array|string $options 短縮名（文字列）または短縮名の配列、もしくはベンダー仕様のオプション配列
     * @param mixed $config ベンダーの設定キーまたは設定インスタンス
     */
    public function css(array|string $options = [], $config = null): void
    {
        $options = $this->normalizeOptions($options);
        parent::css($options, $config);
    }

    /**
     * 短縮名指定をベンダー仕様のオプションに正規化します。
     * - $options が配列（リスト）の場合（例: ['login_index', 'dashboard_home']）は
     *   それぞれを実パスへ解決し、'files' / 'devEntries' に同一配列として設定します。
     * - $options が文字列（例: 'login_index'）の場合は単一のエントリとして扱います。
     * - 既に 'files' / 'devEntries' が指定されている場合は、その各要素を短縮名から解決します。
     *
     * @param array|string $options 短縮名、短縮名の配列、またはベンダー仕様のオプション配列
     * @return array ベンダーのヘルパーに渡せるオプション配列
     */
    protected function normalizeOptions(array|string $options): array
    {
        if (is_string($options)) {
            $file = $this->resolveShorthand($options);
            return [
                'files' => [$file],
                'devEntries' => [$file],
            ];
        }

        // list-style shorthand: ['login_index', 'dashboard_home']
        if ($options !== [] && array_is_list($options)) {
            $files = array_map(fn($n) => $this->resolveShorthand((string)$n), $options);
            return [
                'files' => $files,
                'devEntries' => $files,
            ];
        }

        // options array: resolve within 'files' and 'devEntries' if present
        if (isset($options['files']) && is_array($options['files'])) {
            $options['files'] = array_map(fn($n) => $this->resolveShorthand((string)$n), $options['files']);
        }
        if (isset($options['devEntries']) && is_array($options['devEntries'])) {
            $options['devEntries'] = array_map(fn($n) => $this->resolveShorthand((string)$n), $options['devEntries']);
        }

        return $options;
    }

    /**
     * 'login_index' のような短縮名をビルド対象の実パスへ解決します。
     * 優先順位:
     * - すでに '/' を含む、または拡張子を含む場合はそのまま（先頭の '/' は除去）。
     * - それ以外は '_' を '/' に置換し、baseDir 配下で extensions の順に存在確認します。
     * - どれも存在しない場合は、拡張子 '.ts' を付けたパスをフォールバックとして返します（存在確認は行いません）。
     *
     * @param string $name 短縮名または相対パス
     * @return string 解決後の相対パス（例: resources/js/login/index.tsx）
     */
    protected function resolveShorthand(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return $name;
        }
        // already a path or has extension
        if (str_contains($name, '/') || preg_match('/\.(m?jsx?|tsx?)$/', $name)) {
            return ltrim($name, '/');
        }
        $path = str_replace('_', '/', $name);
        foreach ($this->extensions as $ext) {
            $rel = $this->baseDir . '/' . $path . '.' . $ext;
            $abs = ROOT . DIRECTORY_SEPARATOR . $rel;
            if (is_file($abs)) {
                return $rel;
            }
        }
        // fallback (do not check fs): assume ts
        return $this->baseDir . '/' . $path . '.ts';
    }
}
