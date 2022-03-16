<!DOCTYPE html>
<html>

<head>
	<title>
		<?php echo (isset($title)) ? ucwords($title) :  $this->config->item('web_name'); ?>
	</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/header.css'); ?>">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	<script src="https://kit.fontawesome.com/ca92620e44.js" crossorigin="anonymous"></script>
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.5.0/gsap.min.js"></script>

	<link href="https://unpkg.com/bootstrap-table@1.19.1/dist/bootstrap-table.min.css" rel="stylesheet">
	<script src="https://unpkg.com/bootstrap-table@1.19.1/dist/bootstrap-table.min.js"></script>

	<script src="https://unpkg.com/tableexport.jquery.plugin/tableExport.min.js"></script>
	<script src="https://unpkg.com/bootstrap-table@1.18.3/dist/extensions/export/bootstrap-table-export.min.js"></script>

	<link href="https://unpkg.com/bootstrap-table@1.19.1/dist/extensions/sticky-header/bootstrap-table-sticky-header.css" rel="stylesheet">
	<script src="https://unpkg.com/bootstrap-table@1.19.1/dist/extensions/sticky-header/bootstrap-table-sticky-header.min.js"></script>

	<link rel="icon" href="<?php echo base_url('assets/images/favicon.png') ?>">
	<script type="text/javascript">
		document.onreadystatechange = function() {
			if (document.readyState !== "complete") {
				$(".spinnerdiv").show();
			} else {
				$(".spinnerdiv").fadeOut();
			}
		};
	</script>
</head>

<body>
	<div class="spinnerdiv">
		<div class="spinner-border" style="color:cornflowerblue"></div>
	</div>
	<nav class="navbar navbar-expand-lg navbar-light fixed-top pr-0 pl-0">

		<?php $url = $this->uri->segment(1); ?>

		<div class="logoimg mr-auto ml-3">
			<img src="<?php echo base_url("assets/images/logo_dark.png") ?>" class="navbar-label">
		</div>


		<button class="navbar-toggler" data-target="#coll" data-toggle="collapse">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="coll">
			<ul class="navbar-nav ml-auto">
				<!-- <?php if (!$this->session->userdata('logged_in')) : ?>
					<li class="nav-item">
						<a href="<?php echo base_url('login') ?>" class="nav-link" style="<?php echo ($url == 'login' || $url == '') ? 'border-bottom: 2px solid #fff;' : '' ?>">
							Login
						</a>
					</li>

					<li class="nav-item">
						<a href="<?php echo base_url('register') ?>" class="nav-link" style="<?php echo ($url == 'register') ? 'border-bottom: 2px solid #fff;' : '' ?>">
							Register
						</a>
					</li>
				<?php endif; ?> -->

				<li class="nav-item">
					<a href="<?php echo base_url('home') ?>" class="nav-link" style="<?php echo ($url == 'home') ? 'border-bottom: 2px solid #fff;' : '' ?>">
						Home
					</a>
				</li>

				<li class="nav-item">
					<a href="<?php echo base_url('hits') ?>" class="nav-link" style="<?php echo ($url == 'hits') ? 'border-bottom: 2px solid #fff;' : '' ?>">
						Hits
					</a>
				</li>

				<li class="nav-item">
					<a href="<?php echo base_url('contact-us') ?>" class="nav-link" style="<?php echo ($url == 'contact-us') ? 'border-bottom: 2px solid #fff;' : '' ?>">
						Contact Us
					</a>
				</li>


				<?php if ($this->session->userdata('logged_in')) : ?>
					<li class="nav-item">
						<a href="<?php echo base_url('logout') ?>" class="nav-link text-danger">
							Logout
						</a>
					</li>
				<?php endif; ?>

			</ul>
		</div>

	</nav>

	<div class="container">
		<!-- testing div -->
		<!-- <div class="alerterror">
			<strong>Test notification Lorem, ipsum dolor sit amet consectetur adipisicing elit. Consequatur, ratione repudiandae esse repellendus est expedita, quod aut at odio odit ipsam vel! Lorem, ipsum dolor sitss amet consectetur adipisicing elit. Consequatur, ratione repudiandae esse repellendus est expedita, quod aut at odio odit ipsam vel! Lorem, ipsum dolor sit amet consectetur adipisicing elit. Consequatur, ratione repudiandae esse repellendus est expedita, quod aut at odio odit ipsam vel!</strong>
		</div> -->

		<!-- ajax-failed -->
		<div class="ajax_alert_div ajax_err_div" style="padding:8px;display:none;z-index: 9999;">
			<span class="ajax_err_div_close">&times;</span>
			<strong class="ajax_res_err text-dark"></strong>
		</div>

		<!-- ajax-success -->
		<div class="ajax_alert_div ajax_succ_div" style="padding:8px;display:none;z-index: 9999;">
			<span class="ajax_succ_div_close">&times;</span>
			<strong class="ajax_res_succ text-dark"></strong>
		</div>

		<!-- success-function -->
		<?php if ($this->session->flashdata('valid')) : ?>
			<div class="alertsuccess">
				<strong><?php echo $this->session->flashdata('valid') ?></strong>
			</div>
		<?php endif; ?>

		<!-- failed-function -->
		<?php if ($this->session->flashdata('invalid')) : ?>
			<div class="alerterror">
				<strong><?php echo $this->session->flashdata('invalid') ?></strong>
			</div>
		<?php endif; ?>

		<?php if (validation_errors()) : ?>
			<div class="alerterror">
				<strong><?php echo validation_errors(); ?></strong>
			</div>
		<?php endif; ?>
	</div>

	<div id="content" style="margin-top:60px;">