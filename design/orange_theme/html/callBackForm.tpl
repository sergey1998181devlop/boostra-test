
<script>
    function getNameThem(){
        let n = document.getElementById("select").options.selectedIndex;
        let text = document.getElementById("select").options[n].text;
        document.getElementById('nameThem').value = text;
    }
</script>

<script src="/js/jquery.maskedinput.min.js" type="text/javascript"></script>

<script>
   $(function(){
        $("#phone").mask("+7(999) 999-99-99");
    });
</script>

<style>
    .text-right{
        text-align: right !important;
    }
    .text-left{
        text-align: left !important;
    }
    .text-center{
        text-align: center !important;
    }
    .file{
        display:flex;
        align-items: center;
        cursor: pointer;
    }
    .file:hover{
        text-decoration: underline;
    }
    .file svg{
        margin-right: 13px;
        margin-bottom: 5px;
    }
    .file-block{
        display:flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 10px !important;
        padding-bottom: 50px !important;
    }
    .file-block .file-right{
        display:flex;
        align-items: center;
        width: fit-content;
        width: -moz-fit-content;
    }
    .file-block label{
        line-height: 1;
        margin: 0 !important;
        height: 100%;
        width: 145px !important;
        display: flex;
        padding: 0 !important;
    }
    .file-delete{
        background: #E5613E;
        border-radius: 50px;
        width: 42px;
        height: 42px;
        position: relative;
        display: none;
        margin-right: 35px;

    }
    .file-delete-active{
        display: block;
    }
    .file-delete::before,
    .file-delete::after{
        content: "";
        width: 20px;
        height: 1px;
        background: #fff;
        position: absolute;
        top: 50%;
        left: 50%;
        cursor: pointer;
    }
    .file-delete::before{
        transform: translate(-50%, -50%) rotate(45deg)
    }
    .file-delete::after{
        transform: translate(-50%, -50%) rotate(135deg)
    }
    .file-name{
        display:none;
    }
    .file-name-active{
        display:block;
    }
    @media (max-width: 800px){
        .file-delete{
        margin-right: 15px;
        }
    }
    @media (min-width: 800px){
        #worksheet #steps fieldset label.w-50{
            width: 50%;
        }
        .file-block,
        .title{
            padding: 0 1.3rem;
        }
        #worksheet #steps fieldset label.label{
            margin: 1.5rem 0;
        }
    }

</style>
<script>
var params = window
    .location
    .search
    .replace('?','')
    .split('&')
    .reduce(
        function(p,e){
            var a = e.split('=');
            p[ decodeURIComponent(a[0])] = decodeURIComponent(a[1]);
            return p;
        },
        {}
    );
    $( document ).ready(function() {
        if(params['test'] == 'y'){
            $('#worksheet').show();
        }
    });

</script>
<script>
    $( document ).ready(function() {
        $('input[name=file]').on('change', function() {
            var name = $(this).get(0).files[0].name;
            var size = $(this).get(0).files[0].size;
            $('.file-name').text(name + " " + size + " kb");
            $('.file-delete').addClass("file-delete-active");
            $('.file-name').addClass("file-name-active");
            console.log($(this).get(0).files[0]);
        });
        $('.file-delete').on('click', function() {
            $('input[name=file]')[0].value = "";
            $('.file-name').text("");
            $('.file-delete').removeClass("file-delete-active");
            $('.file-name').removeClass("file-name-active");
            console.log($('input[name=file]').get(0).files[0]);
        });
    });
</script>
<div id="worksheet">
    <div id="steps">
        <form action="calbackForm" class="hidden" method="POST" enctype="multipart/form-data">
            <div class="callback_block text-center">
                <h4>Обратная связь</h4>
            </div>
            <div id="steps">
                <fieldset style="display: block">
                    <div class="clearfix">
                        <label class="w-50">
                            <input required id="phone" type="tel" name="telephone" placeholder="" value=""/>
                            <span class="floating-label">Номер телефона</span>
                        </label>
                         <label class="w-50">
                            <input required  type="text" name="email"  value=""/>
                            <span class="floating-label">Адрес электронной почты</span>
                        </label>
                    </div>
                    <div class="clearfix">
                        <label class="w-50">
                            <input required id="fio" type="text" name="fio" placeholder="" value=""/>
                            <span class="floating-label">ФИО</span>
                        </label>
                        <label class="w-50">
                            <input required  type="text" name="birthday"  value=""/>
                            <span class="floating-label">Дата рождения</span>
                        </label>
                    </div>
                    <div class="title">Выберите тему обращения</div>
                    <div class="clearfix">
                        <label class="big">
    						<div class="select">
                                <select id="select" name="them" required>
                                    {foreach $thems as $them}
                                        <option value="{$them->emailThem}">{$them->nameThem}</option>
                                    {/foreach}
                                </select>
                            </div>
                            <span class="floating-label">Выберите значения</span>
    					</label>
                    </div>
                     <div class="clearfix">
                        <label class="label big text-left">
                            <div>Введите текст обращения</div>
                            <textarea required name="text" placeholder="Заполните форму" style="width: 100%; border: 1px solid #2C2B39; padding: 18px 11px;"></textarea>
                            <input type="hidden" id="nameThem" name="nameThem" value="">
                        </label>
                    </div>
                      <div class="clearfix">

                        <div class="label big text-left">
                            <div class="title">Добавьте файл к вашему обращению</div>

                            <div class="file-block">
                                <span class="file-name"></span>
                                <div class="file-right">
                                    <div class="file-delete"></div>
                                    <label>
                                        <div class="file">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="20" viewBox="0 0 17 20" fill="none">
                                                <path d="M11.7141 5.49989L4.27568 13.4395C4.00776 13.7208 3.85724 14.1023 3.85724 14.5001C3.85724 14.8979 4.00776 15.2794 4.27568 15.5607C4.54359 15.842 4.90697 16.0001 5.28586 16.0001C5.66476 16.0001 6.02813 15.842 6.29605 15.5607L15.1631 6.12119C15.4285 5.84262 15.6389 5.51191 15.7825 5.14794C15.9261 4.78398 16 4.39388 16 3.99992C16 3.60597 15.9261 3.21587 15.7825 2.8519C15.6389 2.48793 15.4285 2.15723 15.1631 1.87866C14.8978 1.60009 14.5828 1.37912 14.2362 1.22836C13.8895 1.0776 13.518 1 13.1428 1C12.7675 1 12.396 1.0776 12.0493 1.22836C11.7027 1.37912 11.3877 1.60009 11.1224 1.87866L2.2553 11.3182C1.45155 12.1621 1 13.3067 1 14.5001C1 15.6936 1.45155 16.8381 2.2553 17.682C3.05906 18.5259 4.14918 19 5.28586 19C6.42255 19 7.51267 18.5259 8.31643 17.682L15.6428 9.99977" stroke="#303030" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Прикрепить
                                        </div>
                                        <input id="file" type="file" name="file" style="visibility: hidden; width: 0; height: 0;"/>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="clearfix">
                        <div class="text-center">
                            <button class="big" type="submit" onclick="getNameThem();">Отправить</button>
                        </div>
                    </div>
                </fieldset>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function (){
        $("input[name='birthday']").mask("99.99.9999");
    });
</script>
