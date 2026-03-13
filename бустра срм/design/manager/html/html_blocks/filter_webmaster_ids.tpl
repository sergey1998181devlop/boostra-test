{if $webmaster_ids}
    <div class="form-group">
        <div class="mb-3">
            <div class="btn-group btn-block">
                <button class="btn btn-sm btn-primary select-all" type="button">Выбрать всё</button>
                <button class="btn btn-sm btn-info select-no" type="button">Убрать всё</button>
            </div>
        </div>
    </div>
    {foreach $webmaster_ids as $webmaster_id}
        <div class="form-group">
            <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                <input name="filter_webmaster_id[]" type="checkbox" class="custom-control-input" id="filter_webmaster_id_{$webmaster_id@index}" value="{$webmaster_id}"  />
                <label class="custom-control-label" for="filter_webmaster_id_{$webmaster_id@index}">
                    {$webmaster_id}
                </label>
            </div>
        </div>
    {/foreach}
{/if}
{literal}
    <script>
        $('.select-all').on('click', function () {
            $(this).closest('.dropdown-menu')
                .find('input:not([value="all"])')
                .prop('checked', true);
        });

        $('.select-no').on('click', function () {
            $(this).closest('.dropdown-menu')
                .find('input')
                .prop('checked', false);
        });
    </script>
{/literal}
