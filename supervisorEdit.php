<?php
include(dirname(__FILE__) . '/_prepend.php');

use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;

ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <meta name="description" content=""/>
  <meta name="author" content=""/>
  <title>Simple Tk Project - Supervisor Manager</title>

  <!-- Styles -->
  <link href="css/bootstrap.min.css" rel="stylesheet" />
  <link href="css/custom.css" rel="stylesheet" />

  <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <!-- Include all compiled plugins (below), or include individual files as needed -->
  <script src="js/bootstrap.min.js"></script>

</head>
<body>

<nav class="navbar navbar-inverse navbar-fixed-top">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="index.php">tk2simple</a>
    </div>
    <div id="navbar" class="collapse navbar-collapse">
      <ul class="nav navbar-nav">
        <li class="active"><a href="index.php">Home</a></li>
        <li><a href="supervisorManager.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
      </ul>
    </div><!--/.nav-collapse -->
  </div>
</nav>

  <div class="container-fluid">
    <h1><a href="index.php" var="title">Edit Supervisor</a></h1>
    <p>&nbsp;</p>
    <div class="content" var="content">
        <div var="form"></div>
    </div>
    <p>&nbsp;</p>
  </div>

</body>
</html>
<?php
$buff = trim(ob_get_clean());
$template = \Dom\Template::load($buff);
$request = $_REQUEST;

$supervisor = new \App\Db\Supervisor();
if (isset($request['supervisorId'])) {
    $supervisor = \App\Db\SupervisorMap::create()->find($request['supervisorId']);
}

/**
 * @param \Tk\Form $form
 */
function doSubmit($form)
{
    $supervisor = $form->get('supervisor');

    if (!$supervisor instanceof \App\Db\Supervisor) return;

    // Load the object with data from the form using a helper object
    \App\Db\SupervisorMap::create()->mapForm($form->getValues(), $supervisor);
    
    if (!$supervisor->title) {
        $form->addFieldError('title', 'Invalid field value.');
    }
    if (!$supervisor->status) {
        $form->addFieldError('status', 'Invalid field value.');
    }
    if (!$supervisor->firstName) {
        $form->addFieldError('firstName', 'Invalid field value.');
    }
    
    if ($form->hasErrors()) {
        return;
    }
    
    $supervisor->save();
    
    if ($form->getTriggeredEvent()->getName() == 'update')
        \Tk\Uri::create('/supervisorManager.php')->redirect();
    \Tk\Uri::create()->redirect();
}


$form = Form::create('supervisorEdit');
$form->set('supervisor', $supervisor);
$form->addCss('form-horizontal');
$form->setRenderer(new \Tk\Form\Renderer\Dom($form));
$form->getRenderer()->setFieldGroupRenderer(new \Tk\Form\Renderer\FieldGroup($form));

// Tab Group Name
$form->appendField(new Field\Input('title'));
$form->appendField(new Field\Input('firstName'));
$form->appendField(new Field\Input('lastName'));

// Tab Group Details
$form->appendField(new Field\Input('courseId'))->setRequired(true);
$form->appendField(new Field\Input('graduationYear'));
$list = new \Tk\Form\Field\Option\ArrayIterator(array('-- Select --' => '', 'Approved' => 'approved', 'Not Approved' => 'not approved', 'Pending' => 'pending'));
$form->appendField(new Field\Select('status', $list));
$form->appendField(new Field\Checkbox('private'));

$form->appendField(new Event\Button('update', 'doSubmit'));
$form->appendField(new Event\Button('save', 'doSubmit'));
$form->appendField(new Event\Link('cancel', \Tk\Uri::create('/supervisorManager.php')));


$form->load((array)$supervisor);

$form->execute();


// SHOW

$template->appendTemplate('form', $form->getRenderer()->show());

if ($supervisor->title) {
    $template->insertText('title', 'Edit Supervisor: ' . $supervisor->title . ' ' . $supervisor->firstName . ' ' . $supervisor->lastName);
}


echo $template->toString();
