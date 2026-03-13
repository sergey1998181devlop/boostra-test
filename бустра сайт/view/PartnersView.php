<?php

require_once 'View.php';

class PartnersView extends View
{
    public function fetch()
    {
        // === Документы Лорд ===
        $lordDocs = [
            ['name' => '1. Выписка из государственного реестра МФО.pdf', 'path' => '/files/docs/partners/lord/1._ypiska_iz_gosudarstvennogo_reestra_.pdf'],
            ['name' => '2. Выписка из протокола СРО.pdf', 'path' => '/files/docs/partners/lord/2._ypiska_iz_protokola_.pdf'],
            ['name' => '3. Свидетельство ИНН.pdf', 'path' => '/files/docs/partners/lord/3._videtelstvo_.pdf'],
            ['name' => '4. Устав.pdf', 'path' => '/files/docs/partners/lord/4._stav.pdf'],
            ['name' => '5. Политика Обработки персональных данных.pdf', 'path' => '/files/docs/partners/lord/5._olitika_brabotki_personalnykh_dannykh.pdf'],
            ['name' => '6. Соглашение об использовании АСП.pdf', 'path' => '/files/docs/partners/lord/6._oglashenie_ob_ispolzovanii_.pdf'],
            ['name' => '7. Общие условия договора займа.pdf', 'path' => '/files/docs/partners/lord/7._bshchie_usloviya_dogovora_zayma.pdf'],
            ['name' => '8. Правила предоставления займов.pdf', 'path' => '/files/docs/partners/lord/8._ravila_predostavleniya_zaymov.pdf'],
            ['name' => '9. Информация для получателей финансовых услуг.pdf', 'path' => '/files/docs/partners/lord/9._informatsiya_dlya_poluchateley_finansovykh_uslug.pdf'],
            ['name' => '10. Политика конфиденциальности.pdf', 'path' => '/files/docs/partners/lord/10._politika_konfidentsialnosti.pdf'],
            ['name' => '11. Порядок рассмотрения обращений получателей финансовых услуг.pdf', 'path' => '/files/docs/partners/lord/11._poryadok_rassmotreniya_obrashcheniy_poluchateley_finansovykh_uslug.pdf'],
            ['name' => '12. Базовый стандарт защиты прав и интересов получателей финансовых услуг.pdf', 'path' => '/files/docs/partners/lord/12._azovyy_standart_zashchity_prav_i_interesov_poluchateley_finansovykh_uslug.pdf'],
            ['name' => '13. Базовый стандарт по управлению рисками микрофинансовых организаций.pdf', 'path' => '/files/docs/partners/lord/13._azovyy_standart_po_upravleniyu_riskami_mikrofinansovykh_organizatsiy.pdf'],
            ['name' => '14. Базовый стандарт совершения МФО операций на финансовом рынке.pdf', 'path' => '/files/docs/partners/lord/14._azovyy_standart_soversheniya_operatsiy_na_finansovom_rynke.pdf'],
            ['name' => "15. Закон РФ от 07.02.1992 № 2300-1 'О защите прав потребителей'.pdf", 'path' => '/files/docs/partners/lord/15._akon_ot_07.02.1992_2300-1_zashchite_prav_potrebiteley.pdf'],
            ['name' => '16. Информационная брошюра Банка России об МФО.pdf', 'path' => '/files/docs/partners/lord/16._nformatsionnaya_broshyura_anka_ossii_ob_.pdf'],
            ['name' => '17. Информация о подаче обращения в адрес ФУ.pdf', 'path' => '/files/docs/partners/lord/17._nformatsiya_o_podache_obrashcheniya_v_adres_.pdf'],
            ['name' => '18. Информация о рисках доступа к защищаемой информации.pdf', 'path' => '/files/docs/partners/lord/18._nformatsiya_o_riskakh_dostupa_k_zashchishchaemoy_informatsii.pdf'],
            ['name' => '19. Оферта об использовании процессингового центра Best2pay.pdf', 'path' => '/files/docs/partners/lord/19._ferta_ob_ispolzovanii_protsessingovogo_tsentra_best2pay.pdf'],
            ['name' => '20. Политика платежей Best2pay.pdf', 'path' => '/files/docs/partners/lord/20._olitika_platezhey_best2pay.pdf'],
            ['name' => '21. Памятка Банка России о кредитных каникулах для участников СВО.pdf', 'path' => '/files/docs/partners/lord/21._amyatka_anka_ossii_o_kreditnykh_kanikulakh_dlya_uchastnikov_.pdf'],
            ['name' => '22. Информация о кредитных каникулах 353-ФЗ.pdf', 'path' => '/files/docs/partners/lord/22._nformatsiya_o_kreditnykh_kanikulakh_353-.pdf'],
            ['name' => '23. Информация о кредитных каникулах 377-ФЗ.pdf', 'path' => '/files/docs/partners/lord/23._nformatsiya_o_kreditnykh_kanikulakh_377-.pdf'],
            ['name' => '24. Информация для заемщиков о самозапрете.pdf', 'path' => '/files/docs/partners/lord/24._nformatsiya_dlya_zaemshchikov_o_samozaprete.pdf'],
            ['name' => '25. Информация об условиях предоставления, использования и возврата потребительского займа.pdf', 'path' => '/files/docs/partners/lord/25._nformatsiya_ob_usloviyakh_predostavleniya_ispolzovaniya_i_vozvrata_potrebitelskogo_zayma.pdf'],
            ['name' => '26. Информация о структуре и составе акционеров.pdf', 'path' => '/files/docs/partners/lord/26._nformatsiya_o_strukture_i_sostave_aktsionerov.pdf'],
            ['name' => '27. Согласие на использование товарно знака.pdf', 'path' => '/files/docs/partners/lord/27._oglasie_na_ispolzovanie_tovarno_znaka.pdf'],
            ['name' => '28. Информация о лице, осуществляющем функции единоличного исполнительного органа.pdf', 'path' => '/files/docs/partners/lord/28._nformatsiya_o_litse_osushchestvlyayushchem_funktsii_edinolichnogo_ispolnitelnogo_organa.pdf'],
            ['name' => '29. Режим работы и обособленные подразделения.pdf', 'path' => '/files/docs/partners/lord/29._ezhim_raboty_i_obosoblennye_podrazdeleniya.pdf'],
        ];

        // === Документы Русзайм (РЗС) ===
        $rusZaimDocs = [
            ['name' => '1. Свидетельство о внесении сведений в государственный реестр МФО.pdf', 'path' => '/files/docs/partners/rus-zaim/1._videtelstvo_o_vnesenii_svedeniy_v_gosudarstvennyy_reestr_.pdf'],
            ['name' => '2. Свидетельство о вступлении в СРО МиР.pdf', 'path' => '/files/docs/partners/rus-zaim/2._videtelstvo_o_vstuplenii_v_i.pdf'],
            ['name' => '3. Свидетельство ИНН.pdf', 'path' => '/files/docs/partners/rus-zaim/3._videtelstvo_.pdf'],
            ['name' => '4. Устав.pdf', 'path' => '/files/docs/partners/rus-zaim/4._stav.pdf'],
            ['name' => '5. Политика Обработки персональных данных.pdf', 'path' => '/files/docs/partners/rus-zaim/5._olitika_brabotki_personalnykh_dannykh.pdf'],
            ['name' => '6. Соглашение об использовании АСП.pdf', 'path' => '/files/docs/partners/rus-zaim/6._oglashenie_ob_ispolzovanii_.pdf'],
            ['name' => '7. Общие условия договора займа.pdf', 'path' => '/files/docs/partners/rus-zaim/7._bshchie_usloviya_dogovora_zayma.pdf'],
            ['name' => '8. Правила предоставления займов.pdf', 'path' => '/files/docs/partners/rus-zaim/8._ravila_predostavleniya_zaymov.pdf'],
            ['name' => '9. Информация для получателей финансовых услуг.pdf', 'path' => '/files/docs/partners/rus-zaim/9._nformatsiya_dlya_poluchateley_finansovykh_uslug.pdf'],
            ['name' => '10. Политика конфиденциальности.pdf', 'path' => '/files/docs/partners/rus-zaim/10._olitika_konfidentsialnosti.pdf'],
            ['name' => '11. Порядок рассмотрения обращений получателей финансовых услуг.pdf', 'path' => '/files/docs/partners/rus-zaim/11._oryadok_rassmotreniya_obrashcheniy_poluchateley_finansovykh_uslug.pdf'],
            ['name' => '12. Базовый стандарт защиты прав и интересов получателей финансовых услуг.pdf', 'path' => '/files/docs/partners/rus-zaim/12._azovyy_standart_zashchity_prav_i_interesov_poluchateley_finansovykh_uslug.pdf'],
            ['name' => '13. Базовый стандарт по управлению рисками микрофинансовых организаций.pdf', 'path' => '/files/docs/partners/rus-zaim/13._azovyy_standart_po_upravleniyu_riskami_mikrofinansovykh_organizatsiy.pdf'],
            ['name' => '14. Базовый стандарт совершения МФО операций на финансовом рынке.pdf', 'path' => '/files/docs/partners/rus-zaim/14._azovyy_standart_soversheniya_operatsiy_na_finansovom_rynke.pdf'],
            ['name' => "15. Закон РФ от 07.02.1992 № 2300-1 'О защите прав потребителей'.pdf", 'path' => '/files/docs/partners/rus-zaim/15._akon_ot_07.02.1992_2300-1_zashchite_prav_potrebiteley.pdf'],
            ['name' => '16. Информационная брошюра Банка России об МФО.pdf', 'path' => '/files/docs/partners/rus-zaim/16._nformatsionnaya_broshyura_anka_ossii_ob_.pdf'],
            ['name' => '17. Информация о подаче обращения в адрес ФУ.pdf', 'path' => '/files/docs/partners/rus-zaim/17._nformatsiya_o_podache_obrashcheniya_v_adres_.pdf'],
            ['name' => '18. Информация о рисках доступа к защищаемой информации.pdf', 'path' => '/files/docs/partners/rus-zaim/18._nformatsiya_o_riskakh_dostupa_k_zashchishchaemoy_informatsii.pdf'],
            ['name' => '19. Оферта об использовании процессингового центра Best2pay.pdf', 'path' => '/files/docs/partners/rus-zaim/19._ferta_ob_ispolzovanii_protsessingovogo_tsentra_best2pay.pdf'],
            ['name' => '20. Политика платежей Best2pay.pdf', 'path' => '/files/docs/partners/rus-zaim/20._olitika_platezhey_best2pay.pdf'],
            ['name' => '21. Памятка Банка России о кредитных каникулах для участников СВО.pdf', 'path' => '/files/docs/partners/rus-zaim/21._amyatka_anka_ossii_o_kreditnykh_kanikulakh_dlya_uchastnikov_.pdf'],
            ['name' => '22. Информация о кредитных каникулах 353-ФЗ.pdf', 'path' => '/files/docs/partners/rus-zaim/22._nformatsiya_o_kreditnykh_kanikulakh_353-.pdf'],
            ['name' => '23. Информация о кредитных каникулах 377-ФЗ.pdf', 'path' => '/files/docs/partners/rus-zaim/23._nformatsiya_o_kreditnykh_kanikulakh_377-.pdf'],
            ['name' => '24. Информация для клиентов о финансовых услугах и дополнительных услугах, в том числе оказываемых за дополнительную плату.pdf', 'path' => '/files/docs/partners/rus-zaim/24._nformatsiya_dlya_klientov_o_finansovykh_uslugakh_i_dopolnitelnykh_uslugakh_v_tom_chisle_okazyvaemykh_za_dopolnitelnuyu_platu.pdf'],
            ['name' => '25. Информация для заемщиков о самозапрете.pdf', 'path' => '/files/docs/partners/rus-zaim/25._nformatsiya_dlya_zaemshchikov_o_samozaprete.pdf'],
            ['name' => '26. Информация об условиях предоставления, использования и возврата потребительского займа.pdf', 'path' => '/files/docs/partners/rus-zaim/26._nformatsiya_ob_usloviyakh_predostavleniya_ispolzovaniya_i_vozvrata_potrebitelskogo_zayma.pdf'],
            ['name' => '27. Согласие на использование товарного знака.pdf', 'path' => '/files/docs/partners/rus-zaim/27._oglasie_na_ispolzovanie_tovarnogo_znaka.pdf'],
            ['name' => '28. Информация о лице, осуществляющем функции единоличного исполнительного органа.pdf', 'path' => '/files/docs/partners/rus-zaim/28._nformatsiya_o_litse_osushchestvlyayushchem_funktsii_edinolichnogo_ispolnitelnogo_organa.pdf'],
            ['name' => '29. Режим работы и обособленные подразделения.pdf', 'path' => '/files/docs/partners/rus-zaim/29._ezhim_raboty_i_obosoblennye_podrazdeleniya.pdf'],
            ['name' => '30. Информация о структуре и составе акционеров.pdf', 'path' => '/files/docs/partners/rus-zaim/30._nformatsiya_o_strukture_i_sostave_aktsionerov.pdf'],
        ];

        // === Документы Фрида ===
        $fridaDocs = [
            ['name' => '1. Выписка из государственного реестра МФО ЦБ РФ.pdf', 'path' => '/files/docs/partners/frida/1._ypiska_iz_gosudarstvennogo_reestra_.pdf'],
            ['name' => '2. Выписка из протокола СРО.pdf', 'path' => '/files/docs/partners/frida/2._ypiska_iz_protokola_.pdf'],
            ['name' => '3. Свидетельство ИНН.pdf', 'path' => '/files/docs/partners/frida/3._videtelstvo_.pdf'],
            ['name' => '4. Устав.pdf', 'path' => '/files/docs/partners/frida/4._stav.pdf'],
            ['name' => '5. Политика обработки и хранения персональных данных.pdf', 'path' => '/files/docs/partners/frida/5._olitika_obrabotki_i_khraneniya_personalnykh_dannykh.pdf'],
            ['name' => '6. Соглашение об использовании АСП.pdf', 'path' => '/files/docs/partners/frida/6._oglashenie_ob_ispolzovanii_.pdf'],
            ['name' => '7. Общие условия договора займа.pdf', 'path' => '/files/docs/partners/frida/7._bshchie_usloviya_dogovora_zaima.pdf'],
            ['name' => '8. Правила предоставления займов.pdf', 'path' => '/files/docs/partners/frida/8._ravila_predostavleniya_zaimov.pdf'],
            ['name' => '9. Информация для получателей финансовых услуг.pdf', 'path' => '/files/docs/partners/frida/9._informatsiya_dlya_poluchatelei_finansovykh_uslug.pdf'],
            ['name' => '10. Политика конфиденциальности.pdf', 'path' => '/files/docs/partners/frida/10._politika_konfidentsialnosti.pdf'],
            ['name' => '11. Порядок рассмотрения обращений получателей финансовых услуг.pdf', 'path' => '/files/docs/partners/frida/11._oryadok_rassmotreniya_obrashchenii_poluchatelei_finansovykh_uslug.pdf'],
            ['name' => '12. Базовый стандарт защиты прав и интересов получателей финансовых услуг.pdf', 'path' => '/files/docs/partners/frida/12._azovyi_standart_zashchity_prav_i_interesov_poluchatelei_finansovykh_uslug.pdf'],
            ['name' => '13. Базовый стандарт по управлению рисками микрофинансовых организаций.pdf', 'path' => '/files/docs/partners/frida/13._azovyi_standart_po_upravleniyu_riskami_mikrofinansovykh_organizatsii.pdf'],
            ['name' => '14. Базовый стандарт совершения МФО операций на финансовом рынке.pdf', 'path' => '/files/docs/partners/frida/14._azovyi_standart_soversheniya_operatsii_na_finansovom_rynke.pdf'],
            ['name' => "15. Закон РФ от 07.02.1992 № 2300-1 'О защите прав потребителей'.pdf", 'path' => '/files/docs/partners/frida/15._akon_ot_07.02.1992_2300-1_zashchite_prav_potrebitelei.pdf'],
            ['name' => '16. Информационная брошюра Банка России об МФО.pdf', 'path' => '/files/docs/partners/frida/16._nformatsionnaya_broshyura_anka_ossii_ob_.pdf'],
            ['name' => '17. Информация о подаче обращения в адрес ФУ.pdf', 'path' => '/files/docs/partners/frida/17._nformatsiya_o_podache_obrashcheniya_v_adres_.pdf'],
            ['name' => '18. Информация о рисках доступа к защищаемой информации.pdf', 'path' => '/files/docs/partners/frida/18._nformatsiya_o_riskakh_dostupa_k_zashchishchaemoi_informatsii.pdf'],
            ['name' => '19. Памятка Банка России о кредитных каникулах для участников СВО.pdf', 'path' => '/files/docs/partners/frida/19._amyatka_anka_ossii_o_kreditnykh_kanikulakh_dlya_uchastnikov_.pdf'],
            ['name' => '20. Оферта об использовании процессингового центра Best2pay.pdf', 'path' => '/files/docs/partners/frida/20._ferta_ob_ispolzovanii_protsessingovogo_tsentra_best2pay.pdf'],
            ['name' => '21. Политика платежей Best2pay.pdf', 'path' => '/files/docs/partners/frida/21._olitika_platezhei_best2pay.pdf'],
            ['name' => '22. Информация о кредитных каникулах 353-ФЗ.pdf', 'path' => '/files/docs/partners/frida/22._informatsiya_o_kreditnykh_kanikulakh_353-.pdf'],
            ['name' => '23. Информация о кредитных каникулах 377-ФЗ.pdf', 'path' => '/files/docs/partners/frida/23._informatsiya_o_kreditnykh_kanikulakh_377-.pdf'],
            ['name' => '24. Информация для заемщиков о самозапрете.pdf', 'path' => '/files/docs/partners/frida/24._nformatsiya_dlya_zaemshchikov_o_samozaprete.pdf'],
            ['name' => '25. Информация об условиях предоставления, использования и возврата потребительского займа.pdf', 'path' => '/files/docs/partners/frida/25._nformatsiya_ob_usloviyakh_predostavleniya_ispolzovaniya_i_vozvrata_potrebitelskogo_zaima.pdf'],
            ['name' => '26. Информация о лице, осуществляющем функции единоличного исполнительного органа.pdf', 'path' => '/files/docs/partners/frida/26._nformatsiya_o_litse_osushchestvlyayushchem_funktsii_edinolichnogo_ispolnitelnogo_organa.pdf'],
            ['name' => '27. Режим работы и обособленные подразделения.pdf', 'path' => '/files/docs/partners/frida/27._ezhim_raboty_i_obosoblennye_podrazdeleniya.pdf'],
            ['name' => '28. Информация о структуре и составе акционеров.pdf', 'path' => '/files/docs/partners/frida/28._nformatsiya_o_strukture_i_sostave_aktsionerov.pdf'],
        ];

        // Передаём в шаблон
        $this->design->assign('lord_docs', $lordDocs);
        $this->design->assign('rus_zaim_docs', $rusZaimDocs);
        $this->design->assign('frida_docs', $fridaDocs);

        return $this->design->fetch('partners.tpl');
    }
}