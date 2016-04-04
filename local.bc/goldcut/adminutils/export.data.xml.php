<?php
$snapshotNS = TimeOp::now();
$printDebug = true;
XMLExport::exportData($snapshotNS, $printDebug);
?>