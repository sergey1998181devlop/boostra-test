{* Шаблон текстовой страницы *}

{* Канонический адрес страницы *}

{if $technical}

{$meta_title="Технические работы" scope=parent}


<section id="info">
	<div>
		<div class="box">
			<div>
				
                <div style="color:red;text-align:center">
                    <h1>
                        Извините, <br />на сайте ведутся технические работы, <br />личный кабинет недоступен. 
                    </h1>
                    <h3>
                        Попробуйте зайти позже.
                    </h3>
                </div>
                
			</div>
			
            
            
		</div>
	</div>
</section>


{else}

{$meta_title="Сервер перегружен" scope=parent}


<section id="info">
	<div>
		<div class="box">
			<div>
				
                <div style="color:red;text-align:center">
                    <h1>
                        Сервер перегружен. 
                    </h1>
                    <h3>
                        Попробуйте зайти позже.
                    </h3>
                </div>
                
			</div>
			
            
            
		</div>
	</div>
</section>

{/if}