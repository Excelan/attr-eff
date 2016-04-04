package digital.erp.process;

import java.io.IOException;
import java.nio.file.*;
import java.util.*;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;

import digital.erp.symbol.Prototype;
import digital.erp.symbol.PrototypeException;
import digital.erp.symbol.UPN;
import digital.erp.symbol.URN;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.NodeList;
import org.xml.sax.SAXException;

import javax.xml.xpath.*;


public class ManagedProcessesCentral {

    private static ManagedProcessesCentral instance;

    public Map<String, ManagedProcessMastercopy> availableProcessMastercopies;

    public UPN startProcess(Prototype prototype, URN initiator, ManagedProcessExecution returntopme, Prototype subjectPrototype, URN concreteSubject) throws Exception {
        // create MPE
        System.out.println("___ START PROCESS " + prototype + " with initiator " + initiator);
        UPN upn = new UPN(prototype);
        ManagedProcessMastercopy mpm = this.mastercopyByPrototype(prototype);
        if (mpm == null) throw new Exception("No ManagedProcessMastercopy for "+prototype.toString());
        Stage firstStage = mpm.getFirstStage();
        if (firstStage == null) throw new Exception("No first stage in "+prototype.toString());
        ManagedProcessExecution.create(upn, mpm.getFirstStage(), initiator, returntopme, subjectPrototype, concreteSubject);
        // load MPE
        ManagedProcessExecution mpe = ManagedProcessExecution.load(upn);
        mpe.beginCurrentStage();
        return upn;
    }

    public UPN startProcessWithMetadata(Prototype prototype, URN initiator, ManagedProcessExecution returntopme, Prototype subjectPrototype, URN concreteSubject, Map<String, String> metadata) throws Exception {
        // create MPE
        System.out.println("___ START PROCESS (WITH METADATA) " + prototype + " with initiator " + initiator);
        UPN upn = new UPN(prototype);
        ManagedProcessMastercopy mpm = this.mastercopyByPrototype(prototype);
        ManagedProcessExecution.create(upn, mpm.getFirstStage(), initiator, returntopme, subjectPrototype, concreteSubject);
        // load MPE
        ManagedProcessExecution mpe = ManagedProcessExecution.load(upn);
        // metadata
        metadata.forEach((k, v) -> mpe.setMetadataKeyValue(k, v));
        mpe.beginCurrentStage();
        return upn;
    }

    public UPN startProcessConfigured(Prototype prototype, URN initiator, ManagedProcessExecution returntopme, URN parentDocument, Prototype subjectPrototype,  Map<String, String> metadata) throws Exception {
        // create MPE
        System.out.println("___ START PROCESS (CONFIGURED) " + prototype + " with initiator " + initiator);
        // reprocess new version of document
        Integer version = 1;
        if (parentDocument != null) version++;
        // new random mpe id
        UPN upn = new UPN(prototype);
        // get xml for process
        ManagedProcessMastercopy mpm = this.mastercopyByPrototype(prototype);
        // create MPE
        ManagedProcessExecution.create(upn, mpm.getFirstStage(), initiator, returntopme, subjectPrototype, null);
        // load created MPE
        ManagedProcessExecution mpe = ManagedProcessExecution.load(upn);
        // set metadata
        metadata.forEach((k, v) -> mpe.setMetadataKeyValue(k, v));
        // begin first stage
        mpe.beginCurrentStage();
        return upn;
    }

    private ManagedProcessesCentral() throws Exception {
        availableProcessMastercopies = new HashMap<>();
        // read xml specs, build availableProcessMastercopies map
        scanPrototypesXMLSpecs();
    }

    public static ManagedProcessesCentral getInstance() throws Exception {
        if (instance == null) instance = new ManagedProcessesCentral();
        return instance;
    }

    public ManagedProcessMastercopy mastercopyByPrototype(Prototype prototype) throws Exception {
        return availableProcessMastercopies.get(prototype.toString());
    }

    public void registerMastercopyForPrototype(ManagedProcessMastercopy registeredMastercopy, Prototype prototype) {
        availableProcessMastercopies.put(prototype.toString(), registeredMastercopy);
    }

    private void scanPrototypesXMLSpecs() throws IOException, ParserConfigurationException {
        DocumentBuilderFactory dbf = DocumentBuilderFactory.newInstance();
        DocumentBuilder db = dbf.newDocumentBuilder();
        // get all process prototype specs
        final List<Path> files = new ArrayList<>();
        PathMatcher matcher = FileSystems.getDefault().getPathMatcher("glob:*.xml");
        Files.walk(Paths.get("src/main/resources/process")).forEach(filePath -> {
            if (Files.isRegularFile(filePath) && matcher.matches(filePath.getFileName())) {
                files.add(filePath);
            }
        });
        try {
            for (Path filePath : files) {
                ManagedProcessMastercopy mpp = this.buildPrototypeFromXMLXpec(db, filePath);
                if (mpp != null) this.registerMastercopyForPrototype(mpp, mpp.prototype); // skip <process readyfor=""
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    private ManagedProcessMastercopy buildPrototypeFromXMLXpec(DocumentBuilder db, Path filePath) throws IOException, SAXException, NullPointerException, PrototypeException.IncorrectFormat, ManagedProcessException.StageTypeUnknown {

        Document doc = db.parse(filePath.toFile());  // new File(filePath.toString())
        Element processTag = (Element) doc.getElementsByTagName("process").item(0);

        if (processTag.getAttribute("readyfor") == null || Objects.equals(processTag.getAttribute("readyfor"), "")) {
            System.out.println("SKIP " + processTag.getAttribute("prototype")); //
            return null;
        }
        Prototype processPrototype = Prototype.fromString(processTag.getAttribute("prototype"));

        // create MPMastercopy
        ManagedProcessMastercopy mpp = new ManagedProcessMastercopy(processPrototype);

        // build list of Stage
        NodeList nodeList = doc.getElementsByTagName("stages");
        Element elem = (Element) nodeList.item(0);
        nodeList = elem.getElementsByTagName("stage");
        int length = nodeList.getLength();
        String prevStageName = null;

        for (int i = 0; i < length; ++i) {
            Element elStage = (Element) nodeList.item(i);
            String stageName = elStage.getAttribute("name");

            // create process Stage
            Stage stage = new Stage(processPrototype, stageName); // throws NullPointerException
            //System.out.println(elStage.getTagName() + ":" + elStage.getAttribute("name"));
            // get human task/ui
            if (elStage.getAttribute("type") == null)
                System.err.println(elStage.getAttribute("name") + "no type in " + processPrototype.toString());

            if (Objects.equals("humantask", elStage.getAttribute("type"))) {
                stage.setHumantask(true);
                stage.setAutomated(false);
                stage.setProcessAsStage(false);
                //
                XPathFactory xpathFactory = XPathFactory.newInstance();
                XPath xpath = xpathFactory.newXPath();
                try {
                    NodeList respStage = (NodeList) xpath.evaluate("process/responsibility/stage[@name='"+stageName+"']/humantask", doc, XPathConstants.NODESET);
                    Element x = (Element) respStage.item(0);
                    if (x == null) throw new Exception("no humantask tag in humantask stage");
                    String appoint = x.getAttribute("appoint");
                    if (appoint == null) throw new Exception("appoint=null in humantask");
                    stage.setAppoint(appoint);
                    // timelimit
                    String timelimit = x.getAttribute("timelimit");
                    if (timelimit != null) {
                        try {
                            stage.setTimelimit(Integer.parseInt(timelimit));
                        } catch (Exception e) { }
                    }
                } catch (XPathExpressionException e) {
                    e.printStackTrace();
                } catch (Exception e) {
                    e.printStackTrace();
                }
                //

                NodeList nodeListUI = elStage.getElementsByTagName("ui");
                int lengthui = nodeList.getLength();
                if (lengthui > 0) { // <ui exists
                    Element el = (Element) nodeListUI.item(0);
                    //System.out.println(el.getTagName() + ":" + el.getAttribute("task")); // <ui task="formedit">
                }
            } else if (Objects.equals("automated", elStage.getAttribute("type"))) // processing non human stage
            {
                stage.setHumantask(false);
                stage.setAutomated(true);
                stage.setProcessAsStage(false);
            } else if (Objects.equals("delegate", elStage.getAttribute("type"))) // processing non human stage
            {
                stage.setHumantask(false);
                stage.setAutomated(false);
                stage.setProcessAsStage(true);
                // delegate to process prototype
                XPathFactory xpathFactory = XPathFactory.newInstance();
                XPath xpath = xpathFactory.newXPath();
                try {
                    NodeList respStage = (NodeList) xpath.evaluate("process/responsibility/stage[@name='"+stageName+"']/call", doc, XPathConstants.NODESET);
                    Element x = (Element) respStage.item(0);
                    // if process is null then type = delegte but real is humantask
                    String stageCallProcess = x.getAttribute("process");
                    //System.out.println(stageCallProcess);
                    stage.setCallProcessPrototype(Prototype.fromString(stageCallProcess));
                    // timelimit
                    String timelimit = x.getAttribute("timelimit");
                    if (timelimit != null) {
                        try {
                            stage.setTimelimit(Integer.parseInt(timelimit));
                        } catch (Exception e) { }
                    }
                } catch (XPathExpressionException e) {
                    e.printStackTrace();
                }
            } else {
                System.err.println(elStage.getAttribute("name") + " type: " + elStage.getAttribute("type") + " in " + processPrototype.toString());
                throw new ManagedProcessException.StageTypeUnknown(elStage.getAttribute("name") + " type: " + elStage.getAttribute("type") + " in " + processPrototype.toString());
            }
            if (prevStageName != null) mpp.stages.get(prevStageName).setNextstage(stageName);
            else stage.setFirst(true);
            // load dynamic code classes
            stage.loadClasses();
            mpp.stages.put(stageName, stage);
            prevStageName = stageName;
        }
        return mpp;
    }


}
