{* Секция управления компаниями *}
<div class="card">
    <div class="card-header">
        <h4 class="card-title m-0">Управление компаниями</h4>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="companiesTable">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Видимость</th>
                </tr>
                </thead>
                <tbody>
                {foreach $companies as $company}
                    <tr class="company-row" data-id="{$company->id}">
                        <td>{$company->id}</td>
                        <td>{$company->name}</td>
                        <td>
                            <div class="custom-control custom-switch">
                                <input type="checkbox"
                                       class="custom-control-input toggle-company-visibility"
                                       id="toggleCompany{$company->id}"
                                       data-id="{$company->id}"
                                       {if $company->use_in_tickets}checked{/if}
                                <label class="custom-control-label" for="toggleCompany{$company->id}"></label>
                            </div>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>
