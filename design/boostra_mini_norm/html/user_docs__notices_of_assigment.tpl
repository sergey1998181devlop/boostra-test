<section id="cessii_wrapper" class="--hide">
        <div class="tab_wrapper" style="margin-left: 60px">
            <div class="tab_tabs">
                <h2 style="width: max-content">Цессии и Агентские договоры</h2>
                <div id='cessii-table'>
                    <table class="table table-references">
                        {foreach $loan_history as $loan_history_item}
                            <tr>
                                <td>
                                    <a href="#"
                                       class="download-cessii"
                                       data-loan-id="{$loan_history_item->number}"
                                    >{$loan_history_item->number}</a>
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
            .alert-reference {
                font-size: inherit;
                max-width: 100%;
            }
        }
    </style>
    <script type="text/javascript">

        $('#link-cessii').on('click', function( event ){
            $('#cessii_wrapper').toggleClass('--hide');
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

        $('.download-cessii').on('click', function (e) {
            e.preventDefault();
            let loanID = $(this).attr('data-loan-id');

            $.ajax({
                url: "/ajax/get_notice_of_assigment.php?loanID="+loanID,
                dataType: 'json',
                method : 'GET',
                success: function (resp) {
                    if (resp.success) {
                        blob = convertBase64toBlob(resp.return['File'], 'application/pdf');
                        var blobURL = URL.createObjectURL(blob);
                        window.open(blobURL);
                        return;
                    }

                    $(e.target).replaceWith("<div class='alert alert-danger alert-reference'>В данный момент документ не может быть сформирован, просим обратиться с запросом документа по адресу электронной почты info@boostra.ru, в письме обязательно нужно указать ФИО, дату рождения, номер договора и описание необходимого документа</div>");

                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $(e.target).replaceWith("<div class='alert alert-danger alert-reference' >В данный момент документ не может быть сформирован, просим обратиться с запросом документа по адресу электронной почты info@boostra.ru, в письме обязательно нужно указать ФИО, дату рождения, номер договора и описание необходимого документа</div>");
                },
            });
        });
    </script>
{/literal}