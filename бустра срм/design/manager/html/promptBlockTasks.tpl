
                                            <div class="col-lg-4 col-md-6 col-12">
                                                <div class="row edit-block">
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <label class="control-label">ФИО клиента</label>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <input id="clientNameInput" {if $userInfo}value="{$userInfo->lastname} {$userInfo->firstname} {$userInfo->patronymic}"{/if} oninput="task.getUsersByFio(this.value, 'clientName');" type="text" class="form-control" required="true" />
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12" style="position: relative;">
                                                            <div id="clientName" style="position: absolute; z-index: 100000;"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <label class="control-label">Дата рождения</label>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <input id="clientBirthInput" {if $userInfo}value="{$userInfo->birth}"{/if} class="form-control" required="true"/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12" style="position: relative;">
                                                            <div id="clientBirth" style="position: absolute; z-index: 100000;" ></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <label class="control-label">Телeфон клиента</label>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <input id="clientTelInput" {if $userInfo}value="{$userInfo->phone_mobile}"{/if} oninput="task.getUsersByPhone(this.value, 'clientPhone');" id="phoneNumber" type="text" class="form-control" required="true"/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12" style="position: relative;">
                                                            <div id="clientPhone" style="position: absolute; z-index: 100000;"></div>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="userId" id="userId" {if $userInfo}value="{$userInfo->id}"{/if}/>
                                                </div>
                                            </div>
                                            <div class="col-lg-4 col-md-6 col-12">
                                                <div class="row edit-block">
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <label class="control-label">Номер заявки</label>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <input id="bidNumberInput" type="text" oninput="task.getUsersByContractNumber(this.value, 'bidNumber');" class="form-control"/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12" style="position: relative;">
                                                            <div id="bidNumber" style="position: absolute; z-index: 100000;"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <div class="col-md-12">
                                                                <label class="control-label">Номер договора</label>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <input id="creditNumberInput" type="text" oninput="task.getUsersByCreditNumber(this.value, 'creditNumber');"  class="form-control"/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12" style="position: relative;">
                                                            <div id="creditNumber" style="position: absolute; z-index: 100000;"></div>
                                                        </div>
                                                    </div>
                                                    <input id="creditId" type="hidden" name="creditId" value=""/>
                                                </div>
                                            </div>
