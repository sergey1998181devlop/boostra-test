{$canonical="/blog/{$article->slug}" scope=parent}

{$meta_title = "{$article->title}" scope=parent}
{$meta_title2 = "{$article->title}" scope=parent}
{$meta_description = "{$article->description}" scope=parent}
{$meta_keywords = "{$article->keywords}" scope=parent}

<section id="info">
  <div>
    <div class="box">
      <div class="editor-result-content">
        {$article->content}
      </div>
      <div id="contacts">
        <h4>ООО "ФИНТЕХ-МАРКЕТ"</h4>
        <div>
          <div>
            ИНН: 6317164496<br />
            КПП: 631701001<br />
            ОГРН: 1236300023849
          </div>
          <div>
            Расчетный счет: 40702810929180016695<br />
            Банк: ФИЛИАЛ "НИЖЕГОРОДСКИЙ" АО "АЛЬФА-БАНК"<br />
            БИК: 042202824<br />
            Корр. счет: 30101810200000000824<br />
          </div>
          <div>
            Юридический адрес:<br />
            443001, Самарская область, г.о. Самара, вн.р-н Ленинский, г Самара, ул Ярмарочная, д. 3, кв. 62
          </div>
          <div>
            Директор Головкин Павел Романович на основании Устава.
          </div>
        </div>
        <div>Телефон: <a href="tel:88003333073"> 8 800 333 30 73</a></div>
      </div>
      <div id="docs">
        <h4>МКК ООО "Бустра"</h4>
        <ul>
          <li><a target="_blank" href="{$config->root_url}/{$config->docs_files_dir}bazovyj_standart_zashchity_prav_i_interesov_poluchatelej_finansovyh.pdf">Базовый стандарт защиты прав и интересов получателей финансовых</a></li>
          <li><a target="_blank" href="{$config->root_url}/{$config->docs_files_dir}Информационная_брошюра_ЦБ.pdf">Информационная брошюра ЦБ</a></li>
          <li><a target="_blank" href="{$config->root_url}/{$config->docs_files_dir}federalnyj_zakon_n_230.pdf">Федеральный закон № 230</a></li>
        </ul>
      </div>
      <div id="contacts">
        <h4>Контакты</h4>
        <div>
          <div>
            ИНН 6317102210<br/>
            ОГРН 1146317004030<br/>
          </div>
          <div>
            р/с 40701810200000003493
            в АО «Тинькофф Банк» корсчет 30101810145250000974, БИК 044525974
          </div>
          <div>
            Юридический адрес: 443099, Самарская область, г.Самара, ул. Фрунзе, дом 48, оф.10
          </div>
          <div>
            Местонахождение постоянно действующего исполнительного органа Самарская область, г. Самара, Балхашский проезд, дом 11.
          </div>
          <br />
          <div>
            Режим работы:<br />
            понедельник-пятница - с 9-00 до 18-00<br />
            суббота-воскресенье - выходной
          </div>
          <br />
          <div>
            Директор Вороной Игорь Юрьевич на основании Устава.
          </div>
        </div>
        <div>Телефон: <a href="tel:88003333073"> 8 800 333 30 73</a></div>
        {*
                <br />
        <div>
          <div style="display: flex; align-items: center;">
            <img src="design/{$settings->theme|escape}/img/qr_code_1.png" alt="Код QR" width="80" height="80" style="margin-right: 10px;">
            Официальный сайт финансового уполномоченного в сети "Интернет". Финансовый уполномоченный осуществляет досудебное урегулирование споров между потребителями финансовых услуг и финансовыми организациями.
          </div>
          <br />
          <div>
            <div style="display: flex; align-items: center;">
              <img src="design/{$settings->theme|escape}/img/qr_code_2.png" alt="Код QR" width="80" height="80" style="margin-right: 10px;">
              Сайт Федеральной службы судебных приставов в сети "Интернет", содержащий форму для подачи жалоб и обращений на нарушение прав и законных интересов физических лиц при осуществлении деятельности по возврату просроченной задолженности физических лиц, возникшей из денежных обязательств.
            </div>
          </div>

        </div>
                *}
      </div>
    </div>
  </div>
</section>

<style>
  .editor-result-content a {
    color: rgb(0, 102, 204);
  }
  .editor-result-content img {
    width: auto;
    max-width: 100%;
    height: auto;
  }
  .editor-result-content h1 {
    font-size: 23px !important;
  }
  .editor-result-content h2 {
    font-size: 20px !important;
  }
  .editor-result-content h3 {
    font-size: 17px !important;
  }
  .editor-result-content h4 {
    font-size: 14px !important;
  }
  .editor-result-content h5 {
    font-size: 11px !important;
  }
  .editor-result-content h6 {
    font-size: 8px !important;
  }
  .editor-result-content blockquote {
    text-align: center;
    position: relative;
  }
  .editor-result-content blockquote:after {
    content: '"';
  }
  .editor-result-content blockquote:before {
    content: '"';
  }
  .editor-result-content .ql-align-right {
    text-align: right;
  }
  .editor-result-content .ql-align-center {
    text-align: center;
  }
  .editor-result-content .ql-align-justify {
    text-align: justify;
  }
  .editor-result-content img {
    width: 100%;
    max-width: 100%;
    height: auto;
  }
</style>
