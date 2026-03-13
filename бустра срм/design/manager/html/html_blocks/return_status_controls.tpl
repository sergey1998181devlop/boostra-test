{if isset($service_type) && isset($service_id)}
<div class="rr-status-container mb-2"
     data-return-status
     data-service-type="{$service_type}"
     data-service-id="{$service_id}"
     {if $request}
         data-request-id="{$request->id}"
         data-status="{$request->status}"
     {/if}>
    <span class="badge rr-status-badge mr-2 badge-{if $request}{$request->status_badge}{else}info{/if}{if !$request} d-none{/if}"
          data-return-status-badge>
        {if $request}{$request->status_text}{/if}
    </span>
    <button type="button"
            class="btn btn-outline-secondary btn-sm js-return-status-refresh {if !$request || $request->status == 'approved'}d-none{/if}"
            data-request-id="{if $request}{$request->id}{/if}"
            data-service-type="{$service_type}"
            data-service-id="{$service_id}">
        <i class="fa fa-sync"></i>
    </button>
    <div class="text-danger small mt-2 {if !$request || !$request->error_text}d-none{/if}"
         data-return-status-error>
        {if $request}{$request->error_text}{/if}
    </div>
    <small class="text-muted d-block mt-1 {if !$request}d-none{/if}"
           data-return-status-updated>
        {if $request}Обновлено: {$request->updated|date_format:'%d.%m.%Y %H:%M'}{/if}
    </small>
</div>
{/if}

