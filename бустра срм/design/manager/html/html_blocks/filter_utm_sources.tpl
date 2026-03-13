{if $utm_sources}
    <div class="form-group utm_source_btn_group">
        <div class="mb-3">
            <div class="btn-group btn-block">
                <button class="btn btn-sm btn-primary select-all" type="button">Выбрать всё</button>
                <button class="btn btn-sm btn-info select-no" type="button">Убрать всё</button>
            </div>
        </div>
    </div>
    {foreach $utm_sources as $utm_source}
        <div class="form-group">
            <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                <input name="filter_utm_source[]" type="checkbox" class="custom-control-input" id="filter_utm_source_id_{$utm_source@index}" value="{$utm_source}"  />
                <label class="custom-control-label" for="filter_utm_source_id_{$utm_source@index}">
                    {$utm_source}
                </label>
            </div>
        </div>
    {/foreach}
    <div class="form-group">
        <div class="custom-control custom-checkbox mr-sm-2 mb-3">
            <input name="filter_utm_source[]" type="checkbox" class="custom-control-input" id="filter_utm_source_all" value="all"  />
            <label class="custom-control-label" for="filter_utm_source_all">
                Все
            </label>
        </div>
    </div>
{/if}
{if !$filter_ajax_no_load_javascript}
    {literal}
        <script>
            $('.utm_source_btn_group .select-all').on('click', function () {
                $(this).closest('.dropdown-menu')
                    .find('input:not([value="all"])')
                    .prop('checked', true);
            });

            $('.utm_source_btn_group .select-no').on('click', function () {
                $(this).closest('.dropdown-menu')
                    .find('input')
                    .prop('checked', false);
            });
        </script>
    {/literal}
{/if}
