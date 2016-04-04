<?php
extract($this->context);
?>

<input type="hidden" id="initiator" value="<?= $user->urn ?>">
<input type="hidden" id="subjectURN" value="urn:Document:Complaint:C_IW:72452">

<!--  FORM  -->
<textarea placeholder="test" data-selector="claim-claimtext" name="xtext" class="richtext"></textarea>
<form id="managedform"
      data-structure="/config/form/Complaint/Editing/Complaint_C_IW"
      data-load="/universalload/Complaint/Editing/Complaint_C_IW"
      data-save=""
      data-controller="processnext"
      action="/echopost"
      data-managedform="yes"
      data-onsuccess="alertresult1"
      data-onerror="alerterror">
</form>


<div class="buttons">
    <input type="hidden" id="additionalparam" value="valueParam">

    <p class="itext">
        <a class="rin" href="#" data-param='cancel' id="cancelform">Отклонить</a>
    </p>
    <a class="gin" href="#" data-param='vising' id="visingform">Визировать</a>
</div>
<div class="cause">
    <textarea id="cancelformtext" placeholder="Укажите причину отмены"></textarea>
</div>