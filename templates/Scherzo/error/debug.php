<?php

?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap 101 Template</title>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

    <!-- Optional theme -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>

<div class="container">


    <div class="row">
        <div class="col-xs-12">

<h2>Error</h2>
            <div class="panel panel-danger">
                <div class="panel-heading">
<?php echo $e->getMessage(); ?>
                </div>
                <div class="panel-body">
<p>
Line <?php echo $e->getLine(); ?> of
<?php echo basename($e->getFile()); ?><br>
<small><?php echo $e->getFile(); ?></small>
</p>
                </div>
            </div>

        </div>
    </div>




    <div class="row">
        <div class="col-xs-12">
<ul class="list-group">

<?php foreach ($e->getTrace() as $trace) : ?>
<li class="list-group-item">

<p>
<code><?php echo $trace['class']; ?>
<?php echo $trace['type']; ?>
<?php echo $trace['function']; ?>()</code>

<?php if (isset($trace['file'])) : ?>
Line <?php echo $trace['line']; ?> of
<?php echo basename($trace['file']); ?><br>
<small><?php echo $trace['file']; ?></small>
<?php endif; ?>
</p>

<pre>
<?php print_r($trace['args']); ?>
</pre>

</li>
<?php endforeach; ?>
</ul>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">

            <div class="panel panel-default">
                <div class="panel-heading">
<span class="glyphicon glyphicon-chevron-up"></span>&nbsp;
<strong>Included files</strong></div>
                <ul class="list-group">

<?php foreach (get_included_files() as $file) : ?>
                    <li class="list-group-item">
<span class="glyphicon glyphicon-chevron-right"></span>&nbsp;
<?php echo $file; ?>
                    </li>
<?php endforeach; ?>
                </ul>


            </div>




        </div>
    </div>


</div>


<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

<!-- Latest compiled and minified JavaScript -->
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
  </body>
</html>
