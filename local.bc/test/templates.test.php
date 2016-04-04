<?php

function MustacheToLatex()
{
    pendingTest();

  $t = '{{#items}}
Name: {{name}}
Price: {{price}}
{{#features}}
id; {{id}}
Feature: {{description}}
{{/features}}
{{/items}}';

$pr = [
['price'=>'1','name'=>'EXPORT1', 'features' => [ ['id'=>'11','description'=>'EXPORT B'], ['id'=>'12','description'=>'EXPORT C'] ]  ]
];
$items = array('items' => $pr);

  $m = new Mustache_Engine;
  $this->html = $m->render($t, $items);

  $latex = LatexUtils::html2latex($this->html);
  println($latex);
}


  function legacyGCMailTemplates()
  {
    pendingTest();
    $t = '
        <h2>Hello</h2>
        <p>{{user.name}}</p>
        <p>{{ad.title}}</p>
        {% list product %}
        <p>
             product: <span>{{product.id}}</span> title {{product.exporttitle}} <span>end</span>
             NO INNER IN GC TI!
        </p>
        {% end list %}
        ';

    $user = new stdClass();
    $user->name = "MyName";
    $user->email = 'request@attracti.com';

    $pr = [
      ['id'=>'1','exporttitle'=>'EXPORT1', 'inner' => [ ['id'=>'11','title'=>'EXPORT B'], ['id'=>'12','title'=>'EXPORT C'] ]  ]
    ];
    $p = json_decode(json_encode($pr));

    $context = array('user' => $user, 'product' => $p);
      //         ['id'=>'2','exporttitle'=>'EXPORT2']

    $T = new Template($t);
    foreach ($context as $contextKey => $contextValue)
    {
        $T->context->add($contextKey, $contextValue);
    }
    $this->html = (string) $T;

    print '<pre>';
    print htmlentities($this->html);
    print '</pre>';

    $latex = LatexUtils::html2latex($this->html);
    println($latex);

  }

 ?>
