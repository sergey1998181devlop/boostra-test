            <div id="chat" class="tab-pane" role="tabpanel">
                <div class="card">
                    <div class="card-body border-top">
                        <form class="row">
                            <div class="col-2"  style="position: relative;">
                                <button title="Прикрепить мультимедиа" type="button" class="btn btn-info btn-lg" onclick="attachFileDialog();">
                                    <img style="height: 38px; width: auto;" src="{$settings->config->back_url}/chats/icons/paperclip.svg"/>
                                </button>
                                <div id="attachFile" style="display: none; position: absolute; text-align: center;">
                                    <span onclick="attachFoto();" style="cursor: pointer;" title="Прикрепить изображение"><img style="height: 30px; width: auto;" src="{$settings->config->back_url}/chats/icons/foto.svg"/></span>
                                    <span onclick="attachVideo();" style="cursor: pointer;" title="Прикрепить видео"><img style="height: 30px; width: auto;" src="{$settings->config->back_url}/chats/icons/video.svg"/></span>
                                    <span onclick="attachDocument();" style="cursor: pointer;" title="Прикрепить файл"><img style="height: 30px; width: auto;" src="{$settings->config->back_url}/chats/icons/document.svg"/></span>
                                </div>
                            </div>
                            <div class="col-8">
                                <div id="attachFiles" style="text-align: center; padding: 5px;"></div>
                                <textarea id="textMessage" placeholder="Введите текст сообщения" class="form-control b-0" onclick="setInputText();"></textarea>
                            </div>
                            <div class="col-2 text-right" style="position: relative; padding: 5px;">
                                <button type="button" onclick="snedMessage()" class="btn btn-info btn-lg">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                                <div id="sendInMessanger" style="display: none; position: absolute; text-align: center;">
                                    <h5>Отправить в</h5>
                                    <span onclick="sendMessageWhatsApp();" style="cursor: pointer;" title="Отправить в Whats`App"><img style="height: 38px; width: auto;" src="{$settings->config->back_url}/chats/icons/whatsapp.svg"/></span>
                                    <span onclick="sendMessageViber();" style="cursor: pointer;" title="Отправить в Viber"><img style="height: 38px; width: auto;" src="{$settings->config->back_url}/chats/icons/viber.svg"/></span>
                                </div>
                            </div>
                        </form>
                    </div>
                    <script>
                        var clientId = '{$userId}';
                        var MessageTextBlock = 'textMessage';
                    </script>
                    <div class="card-body" id="messangers">
                        {if !$chat}
                            <h3>Чат с клиентом не был открыт</h3>
                        {/if}
                    </div>
                </div>
                <script src="/js/chats.js?v={time()}" type="text/javascript"></script>
            </div>
            <div id="imagePopap" onclick="closePopapImage();" class="modal show" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" style="display: none; padding-right: 15px; height: 100%; width: 100%;" aria-modal="true">
                <div class="modal-body" id="popapImageFull" style="text-align: center;"></div>
            </div>
                
            <div id="uploadImagePopap" class="modal show" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" style="display: none; padding-right: 15px; height: 100%; width: 100%;" aria-modal="true">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Загрузить изображение</h4>
                            <button type="button" class="close" onclick="closeUploadPopap('uploadImagePopap');">×</button>
                        </div>
                        <div class="modal-body">
                            <form name="imageUpload" enctype="multipart/form-data" method="POST">
                                <div>Выберите изображение для загрузки</div>
                                <input name="image" type="file" id="imageInput" accept="image/*" value="">
                                <button onclick="uploadFile('image');" class="btn btn-success waves-effect waves-light">Загрузить</button>
                            </form>
                        </div>
                        <div class="modal-body">
                            <div id="imageMsg"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="uploadDocumentPopap" class="modal show" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" style="display: none; padding-right: 15px; height: 100%; width: 100%;" aria-modal="true">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Загрузить файл</h4>
                            <button type="button" class="close" onclick="closeUploadPopap('uploadDocumentPopap');">×</button>
                        </div>
                        <div class="modal-body">
                            <form name="documentUpload" enctype="multipart/form-data" method="POST">
                                <div>Выберите файл для загрузки</div>
                                <input name="document" type="file" id="documentInput" value="">
                                <button onclick="uploadFile('document');" class="btn btn-success waves-effect waves-light">Загрузить</button>
                            </form>
                        </div>
                        <div class="modal-body">
                            <div id="documentMsg"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="uploadVideoPopap" class="modal show" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" style="display: none; padding-right: 15px; height: 100%; width: 100%;" aria-modal="true">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Загрузить видео</h4>
                            <button type="button" class="close" onclick="closeUploadPopap('uploadVideoPopap');">×</button>
                        </div>
                        <div class="modal-body">
                            <form name="videoUpload" enctype="multipart/form-data" method="POST">
                                <div>Выберите видео для загрузки</div>
                                <input name="video" type="file" id="videoInput"  accept="video/*" value="">
                                <button onclick="uploadFile('video');" class="btn btn-success waves-effect waves-light">Загрузить</button>
                            </form>
                        </div>
                        <div class="modal-body">
                            <div id="videoMsg"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="chatPreloader" class="modal show" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" style="display: none; padding-right: 15px; height: 100%; width: 100%;" aria-modal="true">
                <div style="text-align: center; padding-top: 35vh;">
                    <img style="width: 150px; height: auto;" src="/chats/icons/preloader.gif">
                    <div>
                        Пожалуйста подождите.<br/>
                        Идёт отправка сообщения
                    </div>
                </div>
            </div>