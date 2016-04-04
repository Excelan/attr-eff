package process.DMS.Complaints.Detective;

import digital.erp.data.Entity;
import digital.erp.domains.document.Document;
import digital.erp.domains.document.DocumentMetadata;
import digital.erp.process.ManagedProcessException;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;
import digital.erp.process.Ticket;

public class ProtocolEditingOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("!!! < STAGE OUT ProtocolEditingOut < PROCESS");

        try {
            DocumentMetadata dmd = Document.loadDocumentMetadata(mpe.getSubject());
            dmd.makePublic();
        } catch (Exception e) {
            e.printStackTrace();
        }

        // Жалоба принята/отклонена
        Integer complaintstatus = 0;
        try {
            complaintstatus = Entity.directLoadIntegerForKeyIn("complaintstatus", mpe.getSubject());
        } catch (Exception e) {
            e.printStackTrace();
        }

        System.out.println("??? complaintstatus " + complaintstatus.toString());

        if (complaintstatus == 2)
        {
            try {
                System.out.println("SKIP EXTEND RISK");
                mpe.setNextstage("Vising");
            } catch (ManagedProcessException.StageNotExists stageNotExists) {
                stageNotExists.printStackTrace();
            }
        }

        // TODO оставить право входа в документ (всем остается право видеть документ в списке)
        // TODO можно забрать возможность видеть документ в списке
        // mpe.currentActor > ticket
        // visant tickets (погашаются в decision out)
        // Ticket.allowOpen(false)
        // Ticket.save()


        Ticket ticket = mpe.getTicketForCurrentActor();
        ticket.setAllowknowcuurentstage(true);
        //ticket.setAllowearly(true);
        //ticket.setAllowopen(true);
        /*

        ticket.setAllowseejournal(true);
        ticket.setAllowreadcomments(true);
        ticket.setAllowcomment(true);
        */
//        ticket.setAllowsave(true);
//        ticket.setAllowcomplete(true);
        System.out.println("TICKET SPEC " + ticket);

    }

}

