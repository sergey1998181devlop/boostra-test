<footer>
    {if !in_array($module, ['LoanView', 'AccountView', 'InitUserView'])}
        {if $module|@array_search:['UserView']}
            <section class="d-flex p-5 justify-content-center align-items-center">
                <a class="btn btn-danger" href="/complaint"><i class="bi bi-shield-fill-exclamation me-2"></i>ПОЖАЛОВАТЬСЯ ФИНАНСОВОМУ ОМБУДСМЕНУ</a>
            </section>
        {/if}
        <div class="container-fluid">
            <div class="row justify-content-md-center">
                <div class="col-6 col-md-auto"><a href="info" class="text-uppercase text-decoration-none text-dark">Информация</a></div>
                <div class="col-6 col-md-auto"><a href="info#docs" class="text-uppercase text-decoration-none text-dark">Документы</a></div>
                <div class="col-6 col-md-auto"><a href="covid19" class="text-uppercase text-decoration-none text-dark">COVID-19 поддержка клиентов</a></div>
                <div class="col-6 col-md-auto"><a href="/contacts" class="text-uppercase text-decoration-none text-dark">Контакты</a></div>
                <div class="col-6 col-md-auto"><a href="cooperation_offer" class="text-uppercase text-decoration-none text-dark">Сотрудничество</a></div>
                <div class="col-12 col-md-auto mt-4 mt-md-0">
                 <span class="copy">ООО «ФИНТЕХ-МАРКЕТ» осуществляет деятельность в сфере IT</span>
                </div>
            </div>
        </div>
    {/if}
</footer>
