<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin/partials
 */

$manifestSettings = $data['manifestSettings'];
$cacheSettings    = $data['cacheSettings'];
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<h1>マニフェスト設定</h1>
<form enctype="multipart/form-data" id="pwa-plugin-manifest-setting-form" method="post" action="">
    <ul>
        <li>
            <label>サイト名<input id="test" name="name"
                              value="<?php esc_html_e( $manifestSettings['name'] ); ?>"></label>
        </li>
        <li>
            <label>サイト略称<input id=" test" name="short_name"
                               value="<?php esc_html_e( $manifestSettings['short_name'] ); ?>"></label>
        </li>
        <li>
            <label>start_url
                <input name="start_url" value="<?php esc_html_e( $manifestSettings['start_url'] ); ?>">
            </label>
        </li>
        <li>
            アイコン
            <button type="button" id="add-icon">追加</button>
            <!-- TODO: JSで増やせるようにする & foreachで全部表示する。 -->

            <ul id="icon-list">
				<?php foreach ( $manifestSettings['icons'] as $key => $icon ): ?>
                    <li><label>画像
                            <image src="<?php esc_html_e( $icon['src'] ); ?>" style="width:48px; height:48px;"></image>
                            <input type="file" name="icon_file[]"></label>
                        <label>サイズ
                            <select name="icon_size[]">
                                <option value="">なし</option>
                                <option value="48x48" <?php if ( $icon['sizes'] === '48x48' )
									echo 'selected' ?>>48*48
                                </option>
                                <option value="96x96" <?php if ( $icon['sizes'] === '96x96' )
									echo 'selected' ?>>96*96
                                </option>
                                <option value="144x144" <?php if ( $icon['sizes'] === '144x144' )
									echo 'selected' ?>>144*144
                                </option>
                                <option value="192x192" <?php if ( $icon['sizes'] === '192x192' )
									echo 'selected' ?>>192*192
                                </option>
                            </select>
                        </label>
                    </li>
				<?php endforeach; ?>
				<?php if ( count( $manifestSettings['icons'] ) < 4 ): ?>
                    <li><label>画像<input type="file" name="icon_file[]"></label>
                        <label>サイズ
                            <select name="icon_size[]">
                                <option value="">なし</option>
                                <option value="48x48">48*48</option>
                                <option value="96x96">96*96</option>
                                <option value="144x144">144*144</option>
                                <option value="192x192">192*192</option>
                            </select>
                        </label>
                    </li>
				<?php endif; ?>
            </ul>
        </li>
        <li>
            <label>background_color
                <input name="background_color" value="<?php esc_html_e( $manifestSettings['background_color'] ); ?>">
            </label>
        </li>
        <li>
            <label>description
                <input name="description" value="<?php esc_html_e( $manifestSettings['description'] ); ?>">
            </label>
        </li>
        <li>
            <label>theme_color
                <input name="theme_color" value="<?php esc_html_e( $manifestSettings['theme_color'] ); ?>">
            </label>
        </li>
        <li>
            <label>orientation
                <input name="orientation" value="<?php esc_html_e( $manifestSettings['orientation'] ); ?>">
            </label>
        </li>
        <li>
            <label>display
                <input name="display" value="<?php esc_html_e( $manifestSettings['display'] ); ?>">
            </label>
        </li>
		<?php wp_nonce_field( 'my-nonce-key', 'my-submenu' ); ?>
    </ul>
    <button type="submit">save</button>
</form>
<h1>キャッシュ設定</h1>
<form id="pwa-plugin-cache-setting-form" method="post" action="" class="">
	<?php wp_nonce_field( 'my-nonce-key2', 'my-submenu2' ); ?>
    <ul>
        <li>
            <label>オフラインURL<input name="offline_url"
                                  value="<?php esc_html_e( $cacheSettings['offline_url'] ); ?>"></label>
        </li>
        <li>
            <label>Cache有効期限<input name="ttl" value="<?php esc_html_e( $cacheSettings['ttl'] ); ?>"></label>
        </li>
        <div><label>キャッシュ除外リスト（キャッシュしないURL一覧（正規表現））</label>
            <button type="button" id="add-exclusions">追加</button>
            <ul id="exclusion-list">
				<?php foreach ( $cacheSettings['exclusions'] as $item ): ?>
                    <li><input name="exclusions[]" value="<?php esc_html_e( $item ); ?>"></li>
				<?php endforeach; ?>
                <li><input name="exclusions[]"></li>
            </ul>
        </div>
        <div><label>初期キャッシュ（最初にアプリ起動したときにキャッシュするURL一覧）</label>
            <button type="button" id="add-initial-caches">追加</button>
            <ul id="initial-cache-list">
	            <?php foreach ( $cacheSettings['initial-caches'] as $item ): ?>
                    <li><input name="initial-caches[]" value="<?php esc_html_e( $item ); ?>"></li>
	            <?php endforeach; ?>
                <li><input name="initial-caches[]"></li>
            </ul>
        </div>
    </ul>
    <button type="submit">save</button>
</form>
