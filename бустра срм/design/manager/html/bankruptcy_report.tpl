{$meta_title=$title scope=parent}

<style>
    tr.small td {
        padding: 0.25rem;
    }
    .table thead th, .table th {
        border: 1px solid;
        font-size: 10px;
    }
    thead.position-sticky {
        top: 0;
        background-color: #272c33;
    }
</style>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>{$title}</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">{$title}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{$title}</h4>
                        <div class="col-6 col-md-4">
                            <button onclick="return download();" type="button" class="btn btn-success">
                                <i class="ti-save"></i>&nbsp;{$download_button_title}
                            </button>
                        </div>
                        <br>
                        {include file='html_blocks/pagination.tpl'}
                        <div id="result" class="">
                            <table class="table table-bordered table-hover">
                                <thead class="position-sticky">
                                    <tr>
                                        {foreach $headings as $heading}
                                            <th>{$heading}</th>
                                        {/foreach}
                                    </tr>
                                </thead>
                                <tbody>
                                    {if $items}
                                        {foreach $items as $item}
                                            <tr>
                                                <td><a href="/client/{$item->user_id}">{$item->fio}</a></td>
                                                <td>{$item->scoring_date}</td>
                                                <td>{$item->bankruptcy_date}</td>
                                            </tr>
                                        {/foreach}
                                    {else}
                                        <tr>
                                            <td colspan="5" class="text-danger text-center">Данные не найдены</td>
                                        </tr>
                                    {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>

<script>
    function download() {
        window.open(
            '{$reportUri}?action=download',
            '_blank'
        );
        return false;
    }
</script>