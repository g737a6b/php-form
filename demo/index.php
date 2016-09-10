<?php
use \MOFG_form\MOFG_form;

mb_internal_encoding("UTF-8");
mb_language("Japanese");

require(__DIR__."/../autoload.php");

if( !session_id() ) session_start();

$items = array(
	"item_text" => array(
		"in_page" => 1,
		"title" => "Text",
		"rule" => array(
			"format" => MOFG_form::FMT_ALPNUM
		),
		"filter" => array(
			MOFG_form::FLT_TRIM,
			MOFG_form::FLT_TO_HANKAKU_ALPNUM
		),
		"required" => true
	),
	"item_select" => array(
		"in_page" => 1,
		"title" => "Select",
		"required" => true
	),
	"item_radio" => array(
		"in_page" => 1,
		"title" => "Radio",
		"required" => true
	),
	"item_checkbox" => array(
		"in_page" => 1,
		"title" => "Checkbox",
		"required" => true
	),
	"item_textarea" => array(
		"in_page" => 1,
		"title" => "Textarea"
	)
);

$Form = new MOFG_form("form_demo", $items, $_POST);

$Form->set_error_message(array(
	MOFG_form::E_REQUIRED => "Required",
	MOFG_form::E_FMT_ALPNUM => "Alphanumeric only"
));

$page = $Form->settle();

if( $page === 3 ){
	$to_string = $Form->construct_text();

	$Form->Mail->add_to("YOUR EMAIL ADDRESS");
	$Form->Mail->set_subject("MOFG_form Submitted");
	$Form->Mail->set_body("MOFG_form Submitted\n\n--------\n\n{$to_string}");
	$Form->Mail->add_header("From: noreply@example.com");
	$Form->Mail->send();

	$Form->end_clean();
}
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1.0" />
<title>MOFG_form DEMO</title>
<link rel="stylesheet" href="css/init.css" />
<link rel="stylesheet" href="css/basic.css" />
</head>
<body>
<div id="wrapper">
	<div id="header">
		<h1 class="fz30 fwb"><a href="./" class="tdn">MOFG_form</a></h1>
		<p>
			<span class="dib">PHP form library.</span>
			<span class="dib"><label for="menu-toggle" class="curp link-like color-01">View features</label>.</span>
		</p>
	</div><!-- #header -->

	<div id="main">
		<p class="mb20 fwb tar">Step <?php echo $page; ?>/3</p>
		<hr />
		<div class="page-contents">
<?php if( $page === 3 ): ?>
			<p class="mt20">Submitted.</p>
			<div class="mt20">
				<pre class="text-preview"><?php echo htmlspecialchars($to_string); ?></pre>
				<p class="tac mt20"><a href="./">Try again</a></p>
			</div>
<?php elseif( $page === 2 ): ?>
			<form action="./" method="post">
				<dl class="form-table">
					<dt class="form-table__t required">Text</dt>
					<dd class="form-table__d"><?php $Form->v("item_text"); ?></dd>

					<dt class="form-table__t required">Select</dt>
					<dd class="form-table__d"><?php $Form->v("item_select"); ?></dd>

					<dt class="form-table__t required">Radio</dt>
					<dd class="form-table__d"><?php $Form->v("item_radio"); ?></dd>

					<dt class="form-table__t required">Checkbox</dt>
					<dd class="form-table__d"><?php $Form->v("item_checkbox"); ?></dd>

					<dt class="form-table__t">Textarea</dt>
					<dd class="form-table__d"><?php $Form->v("item_textarea"); ?></dd>
				</dl>

				<hr />

				<div class="mt20 tac">
					<input type="submit" class="btn-01 btn-01--primary effect-fade-on-hover" name="<?php echo $Form->get_name_for(MOFG_form::CTL_ENTER); ?>" value="Next" />
					<span class="pad">&nbsp;</span>
					<input type="submit" class="btn-01 effect-fade-on-hover" name="<?php echo $Form->get_name_for(MOFG_form::CTL_BACK); ?>" value="Prev" />
				</div>
			</form>
<?php else: ?>
			<form action="./" method="post">
				<dl class="form-table">
					<dt class="form-table__t required">Text</dt>
					<dd class="form-table__d">
						<?php $Form->HTML->text("item_text", array("style" => "width:200px;")); ?>
						<?php $Form->e("item_text"); ?>
					</dd>

					<dt class="form-table__t required">Select</dt>
					<dd class="form-table__d">
						<?php $Form->HTML->select("item_select", array("foo", "bar", "baz", "qux"), "----", array("style" => "width:200px;")); ?>
						<?php $Form->e("item_select"); ?>
					</dd>

					<dt class="form-table__t required">Radio</dt>
					<dd class="form-table__d">
						<?php $Form->HTML->radio("item_radio", array("foo", "bar", "baz", "qux"), array("style" => "margin-right:20px;")); ?>
						<?php $Form->e("item_radio"); ?>
					</dd>

					<dt class="form-table__t required">Checkbox</dt>
					<dd class="form-table__d">
						<?php $Form->HTML->checkbox("item_checkbox", array("foo", "bar", "baz", "qux"), array("style" => "margin-right:20px;")); ?>
						<?php $Form->e("item_checkbox"); ?>
					</dd>

					<dt class="form-table__t">Textarea</dt>
					<dd class="form-table__d">
						<?php $Form->HTML->textarea("item_textarea", array("style" => "width:100%;", "rows" => "6")); ?>
						<?php $Form->e("item_textarea"); ?>
					</dd>
				</dl>

				<hr />

				<div class="mt20 tac">
					<input type="submit" class="btn-01 btn-01--primary effect-fade-on-hover" name="<?php echo $Form->get_name_for(MOFG_form::CTL_ENTER); ?>" value="Next" />
					<span class="pad">&nbsp;</span>
					<input type="submit" class="btn-01 effect-fade-on-hover" name="<?php echo $Form->get_name_for(MOFG_form::CTL_RESET); ?>" value="Reset" />
				</div>
			</form>
<?php endif; ?>
		</div>
	</div><!-- #main -->

	<div id="footer">
		<div><a href="https://github.com/g737a6b/php-form">Download/View Source on Github</a></div>
		<div><a href="#">Read API Documentation</a></div>
		<div class="copyright">(c) 2016 Hiroyuki Suzuki <a href="http://mofg.net/">mofg.net</a></div>
	</div><!-- #footer -->

	<div id="menu">
		<input type="checkbox" id="menu-toggle" class="menu-toggle dn" name="menu-toggle" />
		<div class="contents">
			<div class="mb10 fz14"><label for="menu-toggle" class="link-like">&laquo; Back to DEMO</label></div>
			<div class="fz30 fwb">MOFG_form</div>
			<h2 class="fz20 fwb mt20">Features</h2>
			<p class="mt20">MOFG_form is suited to all web forms.</p>
			<ul class="list-01 mt20">
				<li class="list-01__item">All basic input types (text, select, radio, checkbox, textarea)</li>
				<li class="list-01__item">Unlimited pages</li>
				<li class="list-01__item">Validation</li>
				<li class="list-01__item">Filtering</li>
				<li class="list-01__item">HTML generation</li>
				<li class="list-01__item">Sending email</li>
				<li class="list-01__item">Summarizing submitted form</li>
				<li class="list-01__item">Lightweight</li>
				<li class="list-01__item">Installation using Composer</li>
				<li class="list-01__item">MIT Licence</li>
			</ul>
		</div>
	</div><!-- #menu -->
</div><!-- #wrapper -->
<script>
document.onkeydown = function(e){
	if( e.keyCode === 27 ) document.getElementById("menu-toggle").checked = false;
};
</script>
</body>
</html>
