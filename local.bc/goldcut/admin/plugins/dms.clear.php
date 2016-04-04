<?php
if (ADMIN_AREA !== true) {
    require dirname(__FILE__) . '/../boot.php';
}

$dblink = DBPGSQL::link();

foreach (['ManagedProcess_Journal_Record', 'ManagedProcess_Execution_Record', 'Feed_MPETicket_InboxItem', 'DMS_DecisionSheet_Signed', 'Communication_Comment_Level2withEditingSuggestion', 'DMS_Copy_Controled', 'DMS_Copy_Uncontrolled', 'DMS_Document_Universal', 'Event_ProcessExecutionPlanned_Staged', 'RiskManagement_Risk_NotApproved'] as $table) {
    $q = "DELETE FROM \"$table\"";
    println($q, 2);
    $dblink->raw_query($q);
}

println("Ход процессов, комментарии, н/риски, копии очищены. ?doit=yes Чтобы очистить все документы");

foreach (Entity::each_managed_entity() as $m => $es) {
    foreach ($es as $entity) {
        if ($entity->prototype->getInDomain() != 'Document') {
            continue;
        }
        println($entity, 1, TERM_GRAY);

        try {
            $table = $entity->prototype->toTableName();
            $q = "DELETE FROM \"$table\"";
            println($q, 2);
            if ($_GET['doit'] == 'yes') {
                $dblink->raw_query($q);
            }

            $q = "DELETE FROM \"ManagedProcess_Execution_Record\"";
        } catch (Exception $e) {
            println($e, 1, TERM_RED);
        }
    }
}
