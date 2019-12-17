<!DOCTYPE html>
<html>
<head>
	<title>HTML to API - Invoice</title>
	<link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,300,700&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
	<!-- <link rel="stylesheet" href="sass/main.css" media="screen" charset="utf-8"/> -->
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<meta http-equiv="content-type" content="text-html; charset=utf-8">
	<style type="text/css">
		html, body, div, span, applet, object, iframe,
		h1, h2, h3, h4, h5, h6, p, blockquote, pre,
		a, abbr, acronym, address, big, cite, code,
		del, dfn, em, img, ins, kbd, q, s, samp,
		small, strike, strong, sub, sup, tt, var,
		b, u, i, center,
		dl, dt, dd, ol, ul, li,
		fieldset, form, label, legend,
		table, caption, tbody, tfoot, thead, tr, th, td,
		article, aside, canvas, details, embed,
		figure, figcaption, footer, header, hgroup,
		menu, nav, output, ruby, section, summary,
		time, mark, audio, video {
			margin: 0;
			padding: 0;
			border: 0;
			font: inherit;
			font-size: 100%;
			vertical-align: baseline;
		}

		html {
			line-height: 1;
		}

		ol, ul {
			list-style: none;
		}

		table {
			border-collapse: collapse;
			border-spacing: 0;
		}

		caption, th, td {
			text-align: left;
			font-weight: normal;
			vertical-align: middle;
		}

		q, blockquote {
			quotes: none;
		}
		q:before, q:after, blockquote:before, blockquote:after {
			content: "";
			content: none;
		}

		a img {
			border: none;
		}

		article, aside, details, figcaption, figure, footer, header, hgroup, main, menu, nav, section, summary {
			display: block;
		}

		body {
			font-family: 'Source Sans Pro', sans-serif;
			font-weight: 300;
			font-size: 12px;
			margin: 0;
			padding: 0;
		}
		body a {
			text-decoration: none;
			color: inherit;
		}
		body a:hover {
			color: inherit;
			opacity: 0.7;
		}
		body .container {
			min-width: 500px;
			margin: 0 auto;
			padding: 0 20px;
		}
		body .clearfix:after {
			content: "";
			display: table;
			clear: both;
		}
		body .left {
			float: left;
		}
		body .right {
			float: right;
		}
		body .helper {
			display: inline-block;
			height: 100%;
			vertical-align: middle;
		}
		body .no-break {
			page-break-inside: avoid;
		}

		header {
			margin-top: 20px;
			margin-bottom: 50px;
		}
		header figure {
			float: left;
			text-align: center;
		}
		header .company-address {
			float: left;
			max-width: 150px;
			line-height: 1.7em;
		}
		header .company-address .title {
			color: #fdc407;
			font-weight: 400;
			font-size: 1.5em;
			text-transform: uppercase;
		}
		header .company-contact {
		float: right;
		margin-top: 24px;
		font-weight:regular;
		}
		header .company-contact span {
			display: inline-block;
			vertical-align: middle;
		}
		header .company-contact .circle {
			width: 20px;
			height: 20px;
			background-color: white;
			border-radius: 50%;
			text-align: center;
		}
		header .company-contact .circle img {
			vertical-align: middle;
		}
		header .company-contact .phone {
			height: 100%;
			margin-right: 20px;
		}
		header .company-contact .email {
			height: 100%;
			min-width: 100px;
			text-align: right;
		}

		section .details {
			margin-bottom: 55px;
		}
		section .details .client {
			width: 50%;
			line-height: 20px;
		}
		section .details .client .name {
			color: #fdc407;
		}
		section .details .data {
			width: 50%;
			text-align: right;
		}
		section .details .title {
			margin-bottom: 15px;
			color: #fdc407;
			font-size: 3em;
			font-weight: 400;
			text-transform: uppercase;
		}
		section table {
			width: 100%;
			border-collapse: collapse;
			border-spacing: 0;
			font-size: 0.9166em;
		}
		section table .qty, section table .unit, section table .total {
			width: 15%;
		}
		section table .desc {
			width: 55%;
		}
		section table thead {
			display: table-header-group;
			vertical-align: middle;
			border-color: inherit;
		}
		section table thead th {
			padding: 5px 10px;
			background: #fdc407;
			border-bottom: 5px solid #FFFFFF;
			border-right: 4px solid #FFFFFF;
			text-align: right;
			color: white;
			font-weight: 400;
			text-transform: uppercase;
		}
		section table thead th:last-child {
			border-right: none;
		}
		section table thead .desc {
			text-align: left;
		}
		section table thead .qty {
			text-align: center;
		}
		section table tbody td {
    padding: 10px;
    background: #f7e29c;
    text-align: right;
    border-bottom: 5px solid #FFFFFF;
    border-right: 5px solid #fff;
    font-size: 16px;
    color: #000;
}
		section table tbody td:last-child {
			border-right: none;
		}
		section table tbody h3 {
			margin-bottom: 5px;
			color: #b78f0b;
			font-weight: 600;
		}
		section table tbody .desc {
			text-align: left;
		}
		section table tbody .qty {
			text-align: center;
		}
		section table.grand-total {
			margin-bottom: 45px;
		}
		section table.grand-total td {
    padding: 5px 10px;
    border: none;
    color: #000;
    text-align: right;
    font-weight: bold;
}
		section table.grand-total .desc {
			background-color: transparent;
		}
		section table.grand-total tr:last-child td {
			font-weight: 600;
			color: #b78f0b;
			font-size: 1.18181818181818em;
		}

		footer {
    margin-bottom: 20px;
    font-size: 17px;
    color: #000;
    font-weight: normal;
}
		footer .thanks {
			margin-bottom: 40px;
			color: #fdc407;
			font-size: 1.16666666666667em;
			font-weight: 600;
		}
		footer .notice {
			margin-bottom: 25px;
		}
		footer .end {
			padding-top: 5px;
			border-top: 2px solid #fdc407;
			text-align: center;
		}
	</style>
</head>

<body>
	<header class="clearfix">
		<div class="container">
			<figure>
				<img class="logo" style="width: 60px;    margin-right: 10px;" src="https://www.cjclive.com/uploads/images/afff0074db2f4ad1dcd11d82e0cc214d247914cc.png" alt="">
			</figure>
			<div class="company-address">
				<h2 class="title">CJC</h2>
				<p style="font-size: 16px;font-weight: normal;">
					2019 CJCLIVE MEDIA LLC 
				</p>
			</div>
			<div class="company-contact">
				<div class="phone left">
					<a href="" style="font-weight: bolder;font-size: 18px;">{{$email_data['seller']->name}}</a>
				</div>

				<div class="email right">
					<a href="" style="font-weight: bolder;font-size: 18px;">{{$email_data['seller']->email}}</a>
				</div>
			</div>
		</div>
	</header>

	<section style="float:left;width:100%;margin-bottom: 25px;">
		<div class="container">
			<div class="details clearfix">
				<div class="client left" style="width: 50%;line-height: 20px;float:left;">
					<p style="font-size: 16px;font-weight: normal;"><b>INVOICE TO:</b></p>
					<p class="name" style="font-size: 16px;font-weight: normal;">{{$email_data['address']->first_name}} {{$email_data['address']->last_name}}</p>
					<p style="font-size: 16px;font-weight: normal;">{{$email_data['address']->address_1}},{{$email_data['address']->address_2}},{{$email_data['address']->pincode}},{{$email_data['address']->state}}</p>
					<a style="font-size: 16px;font-weight: normal;" href="">{{$email_data['user']->email}}</a>
				</div>
				<div class="data right" style="width: 50%;text-align: right;float:right;">
				@if($email_data['transaction']->payment_status==false)
				<div class="title" style="font-size: 24px;margin-bottom: 0px;">Pending</div>
				@elseif($email_data['transaction']->payment_status==true && $email_data['transaction']->payment_status==false)
				<div class="title" style="font-size: 24px;margin-bottom: 0px;">Varification Pending</div>
				@else
				<div class="title" style="font-size: 24px;margin-bottom: 0px;color:#4CAF50;">Paid</div>
				@endif
					<div class="title" style="font-size: 24px;margin-bottom: 0px;">#invoice{{$email_data['transaction']->id}}</div>
					<div class="date" style="font-size: 16px;font-weight: normal;">
						<?php
                        $date1 = $email_data['transaction']->invoice_date;?>
						Date of Invoice: {{date('d/m/Y',strtotime($date1))}}<br>
						
					</div>
				</div>
			</div>

			<table border="0" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th style="font-size: 18px;" class="desc">Description</th>
						<th style="font-size: 18px;" class="qty">Quantity</th>
						<th style="font-size: 18px;" class="unit">Unit price</th>
						<th style="font-size: 18px;" class="total">Total</th>
					</tr>
				</thead>
				<tbody>
				<?php  $total_price = 0; ?>
				 @foreach($email_data["orders"] as $c)
				 
					<tr>
					<?php $total_price += $c->quantity*$c->price ?>
						<td class="desc"><h3>{{$c->name}}</h3>{{$c->description}}<</td>
						<td class="qty">{{$c->quantity}}<</td>
						<td class="unit">${{$c->price}}</td>
						<td class="total">${{$c->quantity*$c->price}}</td>
					</tr>
					@endforeach
					
				</tbody>
			</table>
			<div class="no-break">
				<table class="grand-total">
					<tbody>
						<tr>
							<td class="desc"></td>
							<td class="qty"></td>
							<td class="unit">SUBTOTAL:</td>
							<td class="total">${{$total_price}}</td>
						</tr>
						<tr>
							<td class="desc"></td>
							<td class="qty"></td>
							<td class="unit">TAX 0.0%:</td>
							<td class="total">$00.00</td>
						</tr>
						<tr>
							<td class="desc"></td>
							<td class="unit" colspan="2">GRAND TOTAL:</td>
							<td class="total">${{$total_price}}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</section>

	<footer style="float:left;width:100%">
		<div class="container">
			<div class="thanks">Thank you!</div>
			<div class="notice">
				<div style="margin-bottom:15px;"><b>Payment Instruction:</b></div>
				<div>{{$email_data['transaction']->seller_remark}}.</div>
			</div>
			<div class="end">Invoice was created on a computer and is valid without the signature and seal.</div>
		</div>
	</footer>

</body>

</html>
