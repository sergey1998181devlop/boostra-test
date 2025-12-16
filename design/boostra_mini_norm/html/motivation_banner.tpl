{if $motivation_banner['show']}
    <div id="motivation_banner">
        <div class="description_banner">
            <h4>Ваш уровень лояльности <span class="level anime_text">{$motivation_banner['level']}</span></h4>
            <p>{$motivation_banner['text']}</p>
        </div>
        <div class="background_banner">
            <img src="design/{$settings->theme|escape}/img/content/banner_{$motivation_banner['level_img']|lower}.png" alt="Boostra" />
            {if $motivation_banner['description']}
                <p>{$motivation_banner['description']}</p>
            {/if}
        </div>
    </div>
    <style>
        #motivation_banner {
            color: #282735;
            max-width: 675px;
            margin-bottom: 2rem;
            position: relative;
        }
        #motivation_banner .background_banner p {
            font-size: 12px !important;
            margin: 0 !important;
            text-align: left;
        }
        #motivation_banner p small {
            font-family: Arial sans-serif;
        }
        #motivation_banner .background_banner {
            text-align: right;
        }
        #motivation_banner .background_banner img {
            max-width: 100%;
        }
        #motivation_banner .description_banner {
            position: absolute;
            max-width: 340px;
            top: 35px;
            left: 0px;
        }
        #motivation_banner .description_banner h4 {
            font-weight: normal;
            font-size: 20px !important;
        }

        #motivation_banner .description_banner {
            --level_main_color: #546c76;
        }

        #motivation_banner .description_banner h4 {
        {if $motivation_banner['level_img']|lower == 'silver'}
            --level_main_color: #546c76;
        {else}
            --level_main_color: #9d8900;
        {/if}
        }

        #motivation_banner .anime_text {
            -webkit-mask-image: linear-gradient(-75deg, rgba(0, 0, 0, .6) 30%, #000 50%, rgba(0, 0, 0, .6) 70%);
            -webkit-mask-size: 200%;
            animation: wave 2s infinite;
            color: var(--level_main_color);
        }

        #motivation_banner .description_banner h4 span {
            font-weight: 700;
            font-size: 32px !important;
        }

        #motivation_banner .description_banner p {
            font-weight: 400;
            line-height: 1.125 !important;
            font-size: 20px !important;
            margin: 0.5rem 0 !important;
        }
        @media screen and (max-width: 768px) {
            #motivation_banner {
                display: flex;
                flex-flow: column;
            }
            #motivation_banner .description_banner {
                position: initial;
                max-width: initial;
                margin: 2rem 0;
            }
            #motivation_banner .background_banner {
                text-align: center;
            }
        }
        @-webkit-keyframes wave {
            from {
                -webkit-mask-position: 150%;
            }
            to {
                -webkit-mask-position: -50%;
            }
        }
    </style>
{/if}
