package process.DMS.Regulation.Study;

import digital.erp.data.Entity;
import digital.erp.domains.document.Document;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageIn;
import digital.erp.symbol.Prototype;
import digital.erp.symbol.URN;
import digital.erp.symbol.URNExceptions;

import java.sql.SQLException;

public class CreateDraftIn implements StageIn {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("!!! > Study IN CREATE_DRAFT");
        try {

            // OLD MANUAL // URN subjectURN = Entity.createDraftBy(new URN(Prototype.fromString("ClaimsManagement:Claims:Claim")), 100); // initiator?

            // TA (subject)
            String subjectPrototype = "Document:Regulations:TA";

            URN sopURN = null;

            // <- SOP
            String sopurnstr = mpe.getMetadataValueByKey("sop");
            if (sopurnstr != null) {
                // невозможно, тк процесс уже начат
                // TODO right way
                System.out.println("SOP IN METADATA");
                sopURN = new URN(sopurnstr);
            }
            else
            {
                // HACK
                System.out.println("SOP IN MPE SUBJECT");
                sopURN = mpe.getSubject();
            }
            mpe.setMetadataKeyValue("sop", sopURN.toString());

            URN subjectURN = Document.createDraftForProcessBy(new URN(Prototype.fromString(subjectPrototype)), mpe.getUPN(), mpe.getCurrentactor());
            System.out.println("TA SUBJECT DRAFT " + subjectURN);
            mpe.setSubject(subjectURN);
            mpe.saveSubject(subjectURN);

            // SOP -> TA установить ссылку на SOP в программе обучения TA
            if (sopURN != null)
                Entity.directUpdateLong(subjectURN, "DocumentRegulationsSOP", sopURN.getId());

        } catch (SQLException e) {
            e.printStackTrace();
        } catch (URNExceptions.IncorrectFormat incorrectFormat) {
            incorrectFormat.printStackTrace();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

}
