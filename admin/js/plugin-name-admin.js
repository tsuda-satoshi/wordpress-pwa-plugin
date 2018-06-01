(function ($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
	 *
	 * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
	 *
	 * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */
    $(function() {
        $('#add-icon').on('click', function() {
            const $iconList = $('#icon-list');
            if ($iconList.children().length >= 4) {
                return;
            }
            $iconList.append(`<li><label>画像<input type="file" name="icon_file[]"></label>
                    <label>サイズ
                        <select name="icon_size[]">
                            <option value="">なし</option>
                            <option value="48x48">48*48</option>
                            <option value="96x96">96*96</option>
                            <option value="144x144">144*144</option>
                            <option value="192x192">192*192</option>
                        </select>
                    </label>
                </li>`);
        });
        $('#add-initial-caches').on('click', function () {
            $('#initial-cache-list').append('<li><input name="initial-caches[]"></li>');
        });
        $('#add-exclusions').on('click', function () {
            $('#exclusion-list').append('<li><input name="exclusions[]"></li>');
        });
    });

})(jQuery);
