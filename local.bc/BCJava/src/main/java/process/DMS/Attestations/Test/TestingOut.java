package process.DMS.Attestations.Test;

import digital.erp.domains.document.Document;
import digital.erp.domains.document.DocumentMetadata;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;
import digital.erp.symbol.UPN;

import java.sql.SQLException;

public class TestingOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("< OUT Testing. Results to ASR");

        UPN parentMPEURN = mpe.getReturntopme();
        try {
            ManagedProcessExecution mpeAttestation = ManagedProcessExecution.load(parentMPEURN);

            // сформировать список сдавших и несдавших, сохранить в родительском ASR
            // TODO php gate


            // на основании списков установить в родительском ASR опцию необходимости еще одной сессии сдачи

            //String decision = mpe.getMetadataValueByKey("decision");
            //mpeAttestation.setMetadataKeyValue("passed", "SOME");
            //
            //DocumentMetadata dmd = Document.loadDocumentMetadata(mpeAttestation.getSubject());
            //dmd.makeDone();
        } catch (SQLException e) {
            e.printStackTrace();
        } catch (Exception e) {
            e.printStackTrace();
        }


    }

}
