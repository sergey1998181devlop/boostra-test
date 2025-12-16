<table width="530">
	<tbody>
		<tr>
			<td>
				<table width="530" border="1" cellspacing="1" cellpadding="6">
					<tbody align="center">
						<tr>
							<td >
								<p align="center">
                                    <img src="{$config->root_url}/theme/site/i/contract_qr.jpg" width="120" height="120">
                                </p>
							</td>
							<td>
								<p>ПОЛНАЯ СТОИМОСТЬ ЗАЙМА:</p>
								<p>{$base_percent*365}%</p>
								<p>({($base_percent*365)|percent_string|upper}) ПРОЦЕНТОВ ГОДОВЫХ</p>
							</td>
							<td>
								<p>ПОЛНАЯ СТОИМОСТЬ ЗАЙМА:</p>
								<p>{$return_amount_percents} РУБЛЕЙ</p>
								<p>{$return_amount_percents|price_string|upper}</p>
								<p></p>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
        </tr>
        <tr>
            <td>
            	<p align="center"><strong>ДОПОЛНИТЕЛЬНОЕ СОГЛАШЕНИЕ К ДОГОВОРУ ПОТРЕБИТЕЛЬСКОГО ЗАЙМА № {$number}</strong></p>
				<table>
					<tbody>
						<tr>
							<td>
								<p>Заемщик: {$lastname} {$firstname} {$patronymic}, дата рождения {$birth}, паспорт
									гражданина Российской Федерации; серия {$passport_series} номер {$passport_number}, выдан {$passport_issued}
									зарегистрирован по адресу: {$regaddress_full}</p>
								<p>Кредитор: Общество с ограниченной ответственностью микрокредитная компания ""На
									личное+"" (сокращенное наименование ООО МКК "На личное+"), ОГРН
									1196313019066,созданное и действующее в соответствии с законодательством Российской
									Федерации. Адрес: 443058, Самарская обл. г. Самара, ул. Победы 86, оф.2.1, заключили
									настоящее дополнительное соглашение (далее Соглашение) о нижеследующем:</p>
								<p><span style="text-decoration: line-through;">1. Изложить п. 1 в следующей
										редакции:</span></p>
								<table border="1" width="100%" cellspacing="0" cellpadding="2">
									<tbody>
										<tr>
											<td>
												<p>№ п/п</p>
											</td>
											<td>
												<p>Условие</p>
											</td>
											<td>
												<p>Содержание условия</p>
											</td>
										</tr>
										<tr>
											<td>
												<p>1</p>
											</td>
											<td>
												<p>Сумма займа</p>
											</td>
											<td>
												<p>{$amount} руб. ({$amount|price_string})</p>
											</td>
										</tr>
									</tbody>
								</table>
								<p><span style="text-decoration: line-through;">2</span>1.Изложить п.2 в следующей
									редакции</p>
								<table border="1" width="100%" cellspacing="0" cellpadding="2">
									<tbody>
										<tr>
											<td>
												<p>2</p>
											</td>
											<td>
												<p>Срок действия договора, срок возврата займа</p>
											</td>
											<td>
												<p>До {$return_date_day}день, {$return_date_month}мес., {$return_date_year} год (включительно)</p>
											</td>
										</tr>
									</tbody>
								</table>
								<p>32. Изложить п.6 в следующей редакции:</p>
								<table width="100%" border="1" cellspacing="0" cellpadding="7">

									<tbody>
										<tr>
											<td>
												<p>6</p>
											</td>
											<td>
												<p>Количество, размер и периодичность (сроки) платежей заемщика по
													договору или порядок определения этих платежей</p>
											</td>
											<td>
												<p>Сумма займа и процентов подлежат оплате единовременным платежом в
													срок, указанный в п. 2 Индивидуальных условий.<br />Размер платежа к
													моменту возврата займа {$return_amount} руб. ({$return_amount|price_string})</p>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table>
					<tbody>
						<tr>
							<td>
								<table border="1" cellspacing="0" cellpadding="7">
									<tbody>
										<tr>
											<td>
												<p>Подписано с использованием ПЭП</p>
												<p>{$lastname} {$firstname} {$patronymic}</p>
												<p>{$created|date}</p>
												<p>Телефон {$phone}</p>
												<p>СМС-код {$asp}</p>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>