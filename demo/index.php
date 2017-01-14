<?php
use MofgForm\MofgForm;

mb_internal_encoding("UTF-8");
mb_language("Japanese");

require(__DIR__."/../autoload.php");

if( !session_id() ) session_start();

$items = [
	"item_text" => [
		"in_page" => 1,
		"title" => "Text",
		"rule" => [
			"format" => MofgForm::FMT_ALPNUM
		],
		"filter" => [
			MofgForm::FLT_TRIM,
			MofgForm::FLT_TO_HANKAKU_ALPNUM
		],
		"required" => true
	],
	"item_select" => [
		"in_page" => 1,
		"title" => "Select",
		"required" => true
	],
	"item_radio" => [
		"in_page" => 1,
		"title" => "Radio",
		"required" => true
	],
	"item_checkbox" => [
		"in_page" => 1,
		"title" => "Checkbox",
		"required" => true
	],
	"item_textarea" => [
		"in_page" => 1,
		"title" => "Textarea"
	]
];

$Form = new MofgForm("demo", $items, $_POST);

$Form->set_error_message([
	MofgForm::E_REQUIRED => "Required",
	MofgForm::E_FMT_ALPNUM => "Alphanumeric only"
]);

$page = $Form->settle();

if( $page === 3 ){
	$toString = $Form->construct_text();

	$Form->Mail->add_to("YOUR EMAIL ADDRESS");
	$Form->Mail->set_subject("MofgForm Submitted");
	$Form->Mail->set_body("MofgForm Submitted\n\n--------\n\n{$toString}");
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
<title>MofgForm DEMO</title>
<link rel="stylesheet" href="css/init.css" />
<link rel="stylesheet" href="css/basic.css" />
</head>
<body>
<div id="wrapper">
	<div id="header">
		<h1 class="fz30 fwb"><a href="./" class="tdn">MofgForm</a></h1>
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
				<pre class="text-preview"><?php echo htmlspecialchars($toString); ?></pre>
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
					<input type="submit" class="btn-01 btn-01--primary effect-fade-on-hover" name="<?php echo $Form->get_name_for(MofgForm::CTL_ENTER); ?>" value="Next" />
					<span class="pad">&nbsp;</span>
					<input type="submit" class="btn-01 effect-fade-on-hover" name="<?php echo $Form->get_name_for(MofgForm::CTL_BACK); ?>" value="Prev" />
				</div>
			</form>
<?php else: ?>
			<form action="./" method="post">
				<dl class="form-table">
					<dt class="form-table__t required">Text</dt>
					<dd class="form-table__d">
						<?php $Form->Html->text("item_text", ["style" => "width:200px;"]); ?>
						<?php $Form->e("item_text"); ?>
					</dd>

					<dt class="form-table__t required">Select</dt>
					<dd class="form-table__d">
						<?php $Form->Html->select("item_select", ["foo", "bar", "baz", "qux"], "----", ["style" => "width:200px;"]); ?>
						<?php $Form->e("item_select"); ?>
					</dd>

					<dt class="form-table__t required">Radio</dt>
					<dd class="form-table__d">
						<?php $Form->Html->radio("item_radio", ["foo", "bar", "baz", "qux"], ["style" => "margin-right:20px;"]); ?>
						<?php $Form->e("item_radio"); ?>
					</dd>

					<dt class="form-table__t required">Checkbox</dt>
					<dd class="form-table__d">
						<?php $Form->Html->checkbox("item_checkbox", ["foo", "bar", "baz", "qux"], ["style" => "margin-right:20px;"]); ?>
						<?php $Form->e("item_checkbox"); ?>
					</dd>

					<dt class="form-table__t">Textarea</dt>
					<dd class="form-table__d">
						<?php $Form->Html->textarea("item_textarea", ["style" => "width:100%;", "rows" => "6"]); ?>
						<?php $Form->e("item_textarea"); ?>
					</dd>
				</dl>

				<hr />

				<div class="mt20 tac">
					<input type="submit" class="btn-01 btn-01--primary effect-fade-on-hover" name="<?php echo $Form->get_name_for(MofgForm::CTL_ENTER); ?>" value="Next" />
					<span class="pad">&nbsp;</span>
					<input type="submit" class="btn-01 effect-fade-on-hover" name="<?php echo $Form->get_name_for(MofgForm::CTL_RESET); ?>" value="Reset" />
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
			<div class="fz30 fwb">MofgForm</div>
			<h2 class="fz20 fwb mt20">Features</h2>
			<p class="mt20">MofgForm is suited to all web forms.</p>
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
