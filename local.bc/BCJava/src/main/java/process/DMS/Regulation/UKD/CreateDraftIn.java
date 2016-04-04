package process.DMS.Regulation.UKD;

import digital.erp.data.Entity;
import digital.erp.domains.document.Document;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageIn;
import digital.erp.symbol.Prototype;
import digital.erp.symbol.URN;
import digital.erp.symbol.URNExceptions;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.BarCodeGenerator;
import net.goldcut.utils.Configuration;
import net.goldcut.utils.ZipUtils;

import javax.json.JsonObject;
import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Arrays;

public class CreateDraftIn implements StageIn {

    private void generateCode128(Integer code)
    {
        System.out.println(code);
        BarCodeGenerator.generateCode128(code);
    }

    private void copyFile(String ssource, String sdest) throws IOException {
        File source = new File(ssource);
        File dest = new File(sdest);
        Files.copy(source.toPath(), dest.toPath());
    }

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("!!! > UKD IN CREATE_DRAFT");
        try {

            // for SOP version
            String sopurnstring = mpe.getMetadataValueByKey("sop");
            String asrurnstring = mpe.getMetadataValueByKey("asr");
            String sopversion = mpe.getMetadataValueByKey("sopversion");


            // NEW GET FROM PROCESS START METADATA
            String subjectPrototype = "Document:Protocol:RUKD"; //mpe.getMetadataValueByKey("subjectPrototype");

            URN subjectURN = Document.createDraftForProcessBy(new URN(Prototype.fromString(subjectPrototype)), mpe.getUPN(), mpe.getCurrentactor());
            mpe.setSubject(subjectURN);
            mpe.saveSubject(subjectURN);

            // Additional metadata
            // mpe.setMetadataKeyValue("CreateDraftInVar","In");

            System.out.println("LOOK 1");

            try {
                String json = "{ \"rukdurn\":\"" + subjectURN.toString() + "\", \"sopurn\":\"" + sopurnstring + "\", \"asrurn\":\"" + asrurnstring + "\" }";
                JsonObject gout = HttpRequest.postGetJsonObject(Configuration.host()+"/DMS/UKD/PrepareProtocolRUKD", json);
                System.out.println("Transfer OK " + gout.toString());

                String copyidsstr = gout.getString("copyids");
                ArrayList<String> copyids = new ArrayList<>(Arrays.asList(copyidsstr.split(",")));
                copyids.stream().forEach(copyid -> generateCode128(Integer.parseInt(copyid)));

                // rukd.copy[] each generate latex pdf (sop, copyid, copyholder)
                String json2 = "{ \"rukdurn\":\"" + mpe.getSubject().toString() + "\", \"sopurn\":\"" + sopurnstring + "\", \"copyids\":\"" + copyidsstr + "\" }";
                JsonObject gout2 = HttpRequest.postGetJsonObject(Configuration.host()+"/DMS/UKD/GenerateAllPDFForSOP", json2);
                System.out.println("Genetate PDF OK " + gout2.toString());

                int sleepTime = 4500 * copyids.size();
                System.out.println("Wait for pdf generation: " + sleepTime + " seconds");
                Thread.sleep(sleepTime);

                String zipfolderpath = gout2.getString("zipfolderpath");
                String zipfile = gout2.getString("zipfile");
                String zipURI = gout2.getString("zipuri");
                String pdfsstr = gout2.getString("pdfs");
                String pdfdestsstr = gout2.getString("pdfdests");
                ArrayList<String> pdfs = new ArrayList<>(Arrays.asList(pdfsstr.split(",")));
                ArrayList<String> pdfdests = new ArrayList<>(Arrays.asList(pdfdestsstr.split(",")));

                int i = 0;
                for (String pdf : pdfs){
                    copyFile(pdf, pdfdests.get(i));
                    i++;
                }

                ZipUtils ziputils = new ZipUtils(zipfolderpath, zipfile);
                ziputils.zipIt();

                Entity.directUpdateString(subjectURN, "printarchive", zipURI);

            } catch (Exception e) {
                System.err.println("Transfer, Generate ERROR");
                System.err.println(e.getMessage());
                e.printStackTrace();
            }

        } catch (SQLException e) {
            e.printStackTrace();
        } catch (URNExceptions.IncorrectFormat incorrectFormat) {
            incorrectFormat.printStackTrace();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

}
