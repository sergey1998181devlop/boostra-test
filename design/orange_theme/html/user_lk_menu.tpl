{if $user}
    {if $mobile_menu}
        <div class="col-auto pe-0 align-self-center">
            <div class="btn-group-vertical" role="group" aria-label="Vertical button group">
                <a class="btn btn-primary font-size-small py-0" href="{$lk_url}">Кабинет</a>
                <a class="btn btn-outline-primary font-size-small py-0" href="user/logout">Выход</a>
            </div>
        </div>
    {else}
        <div class="col-xl-auto col">
            <div class="btn-group">
                <div class="dropdown">
                    <button type="button" class="btn btn-primary" id="dropdownMenuLogin"  data-bs-toggle="dropdown" aria-expanded="false">
                        <small>Профиль</small> <i class="bi bi-person"></i>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLogin">
                        <li><a class="dropdown-item text-warning font-size-small py-0" href="{$lk_url}">Личный кабинет</a></li>
                        <li><a class="dropdown-item text-warning font-size-small py-0" href="user/logout">Выход</a></li>
                    </ul>
                </div>
            </div>
        </div>
    {/if}
{else}
    {if $mobile_menu}
        <div class="col-auto pe-0 align-self-center">
            <a href="user/login" type="button" class="btn py-0 btn-primary btn-sm py-0">
                <small>Войти</small>
            </a>
        </div>
    {else}
        <div class="col-xl-auto col">
            <a href="user/login" type="button" class="btn py-0 btn-primary border-2">
                <small>Войти</small>
            </a>
        </div>
    {/if}
{/if}
