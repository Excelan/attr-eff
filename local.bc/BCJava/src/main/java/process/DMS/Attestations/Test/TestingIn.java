package process.DMS.Attestations.Test;

import digital.erp.data.Entity;
import digital.erp.domains.document.Document;
import digital.erp.domains.document.DocumentMetadata;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageIn;
import digital.erp.process.Ticket;
import digital.erp.symbol.UPN;
import digital.erp.symbol.URN;

import java.sql.SQLException;
import java.util.List;

public class TestingIn implements StageIn {

    public void process(ManagedProcessExecution mpe) {
        System.out.println("!!! > Testing In - РАЗДАТЬ ТИКЕТЫ");

        // РАЗДАТЬ ТИКЕТЫ
        UPN parentMPEURN = mpe.getReturntopme();
        try {
            ManagedProcessExecution mpeAttestation = ManagedProcessExecution.load(parentMPEURN);
            URN asr = mpeAttestation.getSubject();
            List<URN> passed = Entity.directLoadURNListForKeyIn("plannedattendees", asr);

            System.out.println(passed.size());

            passed.forEach(student -> System.out.println(student));

            // TODO давать тикеты без расширенных прав, только видеть и заходить на этап (главное - без next)
            passed.forEach(student -> Ticket.createAndActivateAllRightsForActorProcess(student, mpe.getUPN()));

            // TODO Тренеру тикет на Next даст php по Configuration

        } catch (SQLException e) {
            e.printStackTrace();
        } catch (Exception e) {
            e.printStackTrace();
        }

        //

    }

}