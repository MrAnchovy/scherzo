<?php

namespace ScherzoDemo\Controllers;

class DefaultController extends \Scherzo\Core\Controller
{
    public function actionGet($id)
    {
        echo "Demo $id";
    }

    public function actionGet_demo($id)
    {
        echo "Demo action $id";
    }
}
