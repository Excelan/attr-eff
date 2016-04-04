package process.DMS.Complaints.Detective;

import digital.erp.data.Entity;
import digital.erp.domains.document.Document;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageIn;
import digital.erp.symbol.Prototype;
import digital.erp.symbol.UPN;
import digital.erp.symbol.URN;
import digital.erp.symbol.URNExceptions;

import java.sql.SQLException;

public class ProtocolCreateDraftIn implements StageIn {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("> IN Detective.ProtocolCreateDraftIn");
        String ofType = null;
        String parentSubject = null;
        Long parentSubjectId = null;

        try {

            UPN returntopmeUPN = mpe.getReturntopme();
            if (returntopmeUPN != null)
            {
                ManagedProcessExecution returntopme = ManagedProcessExecution.load(returntopmeUPN);
                String parentComplaintPrototypeString = returntopme.getMetadataValueByKey("subjectPrototype");
                Prototype parentComplaintPrototype = Prototype.fromString(parentComplaintPrototypeString);
                switch (parentComplaintPrototype.getOfType()) {
                    case "C_IS":
                        ofType = "C_IS";
                        break;
                    case "C_IV":
                        ofType = "C_IV";
                        break;
                    case "C_IW":
                        ofType = "C_IW";
                        break;
                    case "C_LC":
                        ofType = "C_LC";
                        break;
                    case "C_LP":
                        ofType = "C_LP";
                        break;
                    case "C_LT":
                        ofType = "C_LT";
                        break;
                    case "C_LB":
                        ofType = "C_LB";
                        break;
                }

                URN parentSubjectURN = returntopme.getSubject();
                parentSubject = parentSubjectURN.toString();
                parentSubjectId = parentSubjectURN.getId();
            }

            URN subjectURN = Document.createDraftForProcessBy(new URN(Prototype.fromString("Document:Detective:"+ofType)), mpe.getUPN(), mpe.getCurrentactor());
            mpe.setSubject(subjectURN);
            mpe.saveSubject(subjectURN);

            Entity.directUpdateLong(subjectURN, "DocumentComplaint"+ofType, parentSubjectId);

            mpe.setMetadataKeyValue("parentSubject", parentSubject);


        } catch (SQLException e) {
            e.printStackTrace();
        } catch (URNExceptions.IncorrectFormat incorrectFormat) {
            incorrectFormat.printStackTrace();
        } catch (Exception e) {
            e.printStackTrace();
        }

    }

}