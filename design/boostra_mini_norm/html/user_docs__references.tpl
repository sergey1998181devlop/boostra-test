<section id="references_wrapper" class="--hide">
        <div class="tab_wrapper">
            <div class="tab_tabs">
                <h2>Справки доступные для скачивания</h2>
                <div id = 'reference-table'>
                    <table class="table table-references">
                        {foreach $loan_history as $loan_history_item}
                            {if $loan_history_item->loan_percents_summ == 0 && $loan_history_item->loan_body_summ == 0}
                                <tr>
                                    <td style="text-align: left">
                                        <a href="#"
                                           class="download-reference"
                                           data-loan-id="{$loan_history_item->number}"
                                           data-reference-type="SPRAVKA_O_ZAKRITII"
                                        >{$loan_history_item->number} - Справка о закрытии </a>
                                    </td>
                                </tr>
                            {/if}
                            <tr>
                                <td>
                                    <a href="#"
                                       class="download-reference"
                                       data-loan-id="{$loan_history_item->number}"
                                       data-reference-type="SPRAVKA_O_ZADOLZHENNOSTI"
                                    >{$loan_history_item->number} - Справка о сумме задолженности </a>
                                </td>
                            </tr>
                        {/foreach}


                    </table>
                </div>

            </div>
        </div>

</section>

{literal}
    <style>
        #references_wrapper .tab_wrapper {
            height: auto!important;
            margin-left: 60px;
        }
        .table-references td {
            text-align: left;
            padding-top: 10px;
        }
        .alert-reference {
            padding: 5px 10px!important;
            font-size: 13px;
            margin: 8px 0!important;
            max-width: 600px;
        }
        @media only screen and (max-width: 900px) {
            #references_wrapper .tab_wrapper {
                margin-left: 0;
            }
            .alert-reference {
                font-size: inherit;
                max-width: 100%;
            }
        }
    </style>
    <script type="text/javascript">

        $('#link-references').on('click', function( event ){
            $('#references_wrapper').toggleClass('--hide');
        });


        function convertBase64toBlob(content, contentType) {
            contentType = contentType || '';
            var sliceSize = 512;
            var byteCharacters = window.atob(content);
            var byteArrays = [];

            for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
                var slice = byteCharacters.slice(offset, offset + sliceSize);
                var byteNumbers = new Array(slice.length);

                for (var i = 0; i < slice.length; i++) {
                    byteNumbers[i] = slice.charCodeAt(i);
                }

                var byteArray = new Uint8Array(byteNumbers);
                byteArrays.push(byteArray);
            }

            var blob = new Blob(byteArrays, {
                type: contentType
            });
            return blob;
        }

        $('.download-reference').on('click', function (e) {
            e.preventDefault();
            let loanID = $(this).attr('data-loan-id');
            let referenceType = $(this).attr('data-reference-type');

            $.ajax({
                url: "/ajax/get_references.php?loanID="+loanID+"&referenceType="+referenceType,
                dataType: 'json',
                method : 'GET',
                success: function (resp) {
                    if (resp.success) {
                        blob = convertBase64toBlob(resp.return, 'application/pdf');
                        var blobURL = URL.createObjectURL(blob);
                        window.open(blobURL);
                        return;
                    }

                    $(e.target).replaceWith("<div class='alert alert-danger alert-reference'>В данный момент справка не может быть сформирована, просим обратиться с запросом справки по адресу электронной почты info@boostra.ru, в письме обязательно нужно указать ФИО, дату рождения, номер договора и описание необходимой справки</div>");

                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $(e.target).replaceWith("<div class='alert alert-danger alert-reference' >В данный момент справка не может быть сформирована, просим обратиться с запросом справки по адресу электронной почты info@boostra.ru, в письме обязательно нужно указать ФИО, дату рождения, номер договора и описание необходимой справки</div>");
                },
            });
        });

    </script>
{/literal}