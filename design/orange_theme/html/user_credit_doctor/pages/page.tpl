{* Шаблон страницы Credit Doctor с опросом*}

<style>
    .header_get_a_loan {
        display: none !important;
    }
    header > nav > ul > li:nth-child(2) > ul > li:nth-child(4) {
        display: none !important;
    }
    header > nav > ul > li:nth-child(2) > ul > li:nth-child(5) {
        display: none !important;
    }
    #user_credit_doctor_info h2,  #user_credit_doctor_info h5 {
        margin: 2rem 0;
        text-align: center;
    }
    #docs iframe {
        max-width: 720px;
        margin: auto;
        display: block;
    }
</style>

<div class="panel" id="user_credit_doctor_info">
    <div id="docs" >
        <iframe src="https://view.officeapps.live.com/op/embed.aspx?src={$doc_link}" width='100%' height='720'>
    </div>
</div>
