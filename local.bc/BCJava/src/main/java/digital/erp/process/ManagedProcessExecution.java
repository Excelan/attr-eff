package digital.erp.process;

import digital.erp.symbol.*;
import net.goldcut.database.ConnectionManager;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.Configuration;
import net.goldcut.utils.FileData;
import org.postgresql.util.PGobject;

import java.io.IOException;
import java.sql.*;
import java.util.HashMap;
import java.util.Map;

import java.io.StringReader;
import java.io.StringWriter;
import java.util.Objects;
import javax.json.*;


public class ManagedProcessExecution {

    private UPN upn;
    private URN subject;
    private UPN returntopme;
    private Map<String, String> metadata;
    private String nextstage;
    private String currentstage;
    private URN currentactor;
    private URN initiator;
    private boolean done = false;

    private ManagedProcessMastercopy mpMastercopy;

    private ManagedProcessExecution(UPN upn)
    {
        this.upn = upn;
        this.metadata = new HashMap<>();
        try {
            this.mpMastercopy = ManagedProcessesCentral.getInstance().mastercopyByPrototype(upn.getPrototype());
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public String toString()
    {
        String subjectPart = null;
        if (this.getSubject() != null)
            subjectPart = " subject: "+ this.getSubject().toString();
        else
            subjectPart = " NO_SUBJECT";
        return "MPEXECUTION " + this.upn + "/" + this.getCurrentstage() + " " + this.metadata.toString() + subjectPart;
    }

    public String postTestDataForCurrentStage() throws Exception {
        String data = this.getTestDataForCurrentStage();
        System.err.println(getDataPostPathForCurrentStage());
        return HttpRequest.postGetString(net.goldcut.utils.Configuration.host() + "/universalsave/" + this.getDataPostPathForCurrentStage(), data);
    }

    protected String getTestDataForCurrentStage() throws IOException {
        String path = getTestDataPath();
        String data = FileData.readFile(path);
        data = data.replace("%URN%", this.getSubject().toString());
        System.err.println(data);
        return data;
    }

    public String getDataPostPathForCurrentStage()
    {
        Prototype p = this.getSubject().getPrototype();
        String path = p.getOfClass() + "/" + this.getCurrentstage() + "/" + p.getOfClass() + "_" + p.getOfType();
        return path;
    }

    private String getTestDataPath()
    {
        String path = "../test/data/" + getDataPostPathForCurrentStage() + ".json";
        return path;
    }

    // just create, then need to be loaded
    // TODO set next stage from mastercopy
    // TODO run 1st stage IN
    // TODO for Auto Stage run ProcessBody, complete Stage
    // call from MPC.startProcess(proto)?
    // check Can I start Process?
    // What processes i can start?
    protected static void create(UPN upn, Stage firststage, URN initiator, ManagedProcessExecution returntopme, Prototype subjectPrototype, URN concreteSubject) throws Exception {
        String metadataJson = "{}";
        if (subjectPrototype != null)
            metadataJson = "{\"subjectPrototype\":\""+subjectPrototype.toString()+"\"}";

        try{
        //try (Connection conn = ConnectionManager.getConnection()) {

            Connection conn = ConnectionManager.getConnectionForThread();
            // create, а не prepare потому что есть json в строке запроса
            try (PreparedStatement insertST = conn.prepareStatement("INSERT INTO \"ManagedProcess_Execution_Record\" (id, prototype, initiator, metadata, currentstage, currentactor, done, returntopme, created, subject) VALUES (?, ?, ?, '"+metadataJson+"', ?, ?, false, ?, NOW(), ?)")) {
                insertST.setLong(1, upn.getId());
                insertST.setString(2, upn.getPrototype().toString());
                if (initiator == null)
                    insertST.setNull(3, java.sql.Types.VARCHAR);
                else
                    insertST.setString(3, initiator.toString());
                insertST.setString(4, firststage.getName());
                // first stage actor
                insertST.setNull(5, java.sql.Types.VARCHAR);
                //insertST.setString(5, initiator.toString()); // TODO currenactor of stage != initiator of process
                if (returntopme == null)
                    insertST.setNull(6, java.sql.Types.VARCHAR);
                else
                    insertST.setString(6, returntopme.getUPN().toString());
                if (concreteSubject == null)
                    insertST.setNull(7, java.sql.Types.VARCHAR);
                else
                    insertST.setString(7, concreteSubject.toString());
                int i = insertST.executeUpdate();
                conn.commit();
                System.out.println(">>> inserted new mpe " + upn.getId()); // insertST.toString()
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
        } finally{

        }
    }

    public static ManagedProcessExecution load(UPN upn) throws SQLException {
        return loadFromDatabase(upn, null);
    }

    public void reload() throws SQLException {
        loadFromDatabase(this.getUPN(), this);
    }

    private static ManagedProcessExecution loadFromDatabase(UPN upn, ManagedProcessExecution mpe) throws SQLException {
        //ManagedProcessExecution mpe = null;
        System.out.println("<<< LOAD mpe " + upn.getId() + " " + (mpe == null ? "" : "reload "+mpe.getUPN().getId())); //

        try{
        //try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            try (
                    Statement stat = conn.createStatement();
                    ResultSet rs = stat.executeQuery("SELECT id, prototype, subject, metadata, nextstage, currentstage, currentactor, returntopme, done, initiator FROM \"ManagedProcess_Execution_Record\" WHERE id = " + upn.getId().toString())
            ) {
                if (rs.next()) {
                    try {
                        UPN selfUPN = new UPN(Prototype.fromString(rs.getString("prototype")), rs.getLong("id"));
                        mpe = new ManagedProcessExecution(selfUPN);

                        if (rs.getString("subject") != null) {
                            URN subject = new URN(rs.getString("subject"));
                            mpe.subject = subject;
                        }

                        mpe.setMetadata(rs.getString("metadata")); // через set, тк внутри конверсия в map

                        //mpe.setNextstage(rs.getString("nextstage"));
                        mpe.nextstage = rs.getString("nextstage");

                        mpe.currentstage = rs.getString("currentstage");

                        rs.getString("currentactor");
                        if (rs.wasNull()) mpe.currentactor = null;
                        else mpe.currentactor = new URN(rs.getString("currentactor"));

                        if (rs.getString("returntopme") != null) mpe.returntopme = new UPN(rs.getString("returntopme"));

                        mpe.done = rs.getBoolean("done");

                        rs.getString("initiator");
                        if (rs.wasNull()) mpe.initiator = null;
                        else mpe.initiator = new URN(rs.getString("initiator"));
                    }
                    catch (UPNException.IncorrectFormat e) {
                        conn.rollback();
                        e.printStackTrace();
                    }
                    catch (URNExceptions.IncorrectFormat e) {
                        conn.rollback();
                        e.printStackTrace();
                    } catch (PrototypeException.IncorrectFormat e) {
                        conn.rollback();
                        e.printStackTrace();
                    }
                    //catch (ManagedProcessException.StageNotExists stageNotExists) {
                        //stageNotExists.printStackTrace();
                    //}
                }
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
            conn.commit(); // after result set is closed
        } finally{

        }
        return mpe;
    }


    public Ticket getTicketForCurrentActor()
    {
        URN actor = this.getCurrentactor();
        if (actor == null) return null;
        Ticket ticket = null;
        ticket = Ticket.loadForProcessCurrentActor(this.getUPN(), this.getCurrentactor());
        return ticket;
    }

    // API
    public void completeCurrentStage() throws Exception
    {
        System.out.println("_____ completeCurrentStage " + this.toString());
        if (this.isDone()) throw new ManagedProcessException.OperateOnDone(this.toString());
        Stage currentStage = this.mpMastercopy.stages.get(this.getCurrentstage());
        if (currentStage == null) throw new Exception("No current stage in "+this.toString());
        this.completeStage(currentStage);
        // transition
        // try call next.in = startCurrentStage
    }

    public void completeNestedCurrentStage() throws Exception
    {
        UPN childUPN = new UPN(this.getMetadataValueByKey("child"));
        ManagedProcessExecution mpeChild = ManagedProcessExecution.load(childUPN);
        // TODO add hash of options
        //mpeChild.setMetadataKeyValue("option", "value");
        System.out.println("_____ COMPLETE NESTED " + mpeChild.toString());
        mpeChild.completeCurrentStage();
    }

    // CENTRAL LOGIC
    private void completeStage(Stage stage) throws Exception {

        // CALL OUT

        Ticket ticket = this.getTicketForCurrentActor();
        if (ticket != null) {
            //ticket.setAllowknowcuurentstage(false); // забираем все права, кроме права знать на каком этапе процесс
            ticket.setAllowearly(false);
            ticket.setAllowopen(false);
            ticket.setAllowseejournal(false);
            ticket.setAllowreadcomments(false);
            ticket.setAllowcomment(false);
            ticket.setAllowsave(false);
            ticket.setAllowcomplete(false);
        }

        stage.processOut(this);
        //DMS/SetDocumentState
        try {
            // Внешний вызов, тк в php сработает MQ и будет создан Universal Document
            String json = "{\"state\":\"" + stage.getName() + "\", \"urn\":\"" + this.getSubject() + "\"}";
            String gout = HttpRequest.postGetString(Configuration.host()+"/DMS/SetDocumentState", json);
            System.out.println(gout);
        }
        catch (Exception e)
        {
          System.err.println(e.getMessage());
        }

        // Detect next stage. Try db.nextstage, then stage.nextstage
        System.out.println("---> completeStage " + stage.getName()); //
        Stage nextStage;
        if (this.getNextstage() != null)
        {
            //System.out.print("db.next "); //
            nextStage = this.mpMastercopy.stages.get(this.getNextstage());
        }
        else
        {
            //System.out.print("spec.next "); //
            nextStage = this.mpMastercopy.stages.get(stage.getNextstage());
        }
        //System.out.println(nextStage);
        // Transition to next stage
        if (nextStage != null) {
            System.out.println("~~~ transition->From->To " + stage + " >>> " + nextStage);
            Journaling.record(this, this.getSubject(), stage.getName(), this.getCurrentactor(), Journaling.Direction.OUT, null); // JOURNAL
            this.transitionFromTo(stage, nextStage);
        }
        else {
            System.out.println("/// WILL COMPLETE PROCESS = NO transition (this____________completeProcess_________) From >>> " + stage);
            Journaling.record(this, this.getSubject(), stage.getName(), this.getCurrentactor(), Journaling.Direction.OUT, "{\"procend\":1}"); // JOURNAL
            this.completeProcess();
        }

    }

    // API protected
    protected void beginCurrentStage() throws Exception
    {
        // TODO current actor
        Stage currentStage = this.mpMastercopy.stages.get(this.getCurrentstage());
        if (currentStage == null) throw new Exception("No current stage in "+this.toString());
        // !!! currentStage.startProcessing(this); // run in, process for automated stages
        this.startProcessingStage(currentStage);
    }

    // CENTRAL LOGIC
    protected void startProcessingStage(Stage stage)
    {
        ManagedProcessExecution mpe = this;
        System.out.println("* START PROCESSING " + mpe.toString() + " STAGE: " + stage.toString());
        // in any case start stage IN

        // ACTOR

        // before or after - request current actor (after Out - request next actor if not setted up)
        // first current actor == next from null stage?

        // configuration
        if (stage.isHumantask() && !stage.isProcessAsStage() && Objects.equals(stage.getAppoint(), "configuration")) // not this.isAutomated() || this.isProcessAsStage()
        {
            // stage Actor нужен только для humantask этапов
            String requestParams = "";
            try {
                System.out.println("HTTP HTTP HTTP HTTP HTTP REQUEST ACTOR");
                requestParams = "{\"upn\":\""+mpe.getUPN()+"\",\"processPrototype\":\""+mpe.getUPN().getPrototype().toString()+"\",\"stage\":\""+mpe.getCurrentstage()+"\",\"subjectProto\":\""+mpe.getSubject().getPrototype()+"\"}";
                JsonObject json = HttpRequest.postGetJsonObject(Configuration.host()+"/processactorsdirector", requestParams);
                String stageActorS = json.getString("stageActor");
                if (stageActorS == "urn:Actor:User:System:0")
                    System.err.println("!!!!!!!!!!!!!!!!!!!!!! processactorsdirector returns urn:Actor:User:System:0 for ");
                URN stageActor = new URN(stageActorS);
                mpe.setCurrentactor(stageActor);
                mpe.saveCurrentactor(stageActor);
            } catch (Exception e) {
                e.printStackTrace();
                System.err.println("HTTP HTTP HTTP HTTP HTTP  ERROR");
                System.err.println(requestParams);
                // TODO Процесс застрял на In - currentactor не назначен

            }
        }
        // initiator
        if (stage.isHumantask() && !stage.isProcessAsStage() && Objects.equals(stage.getAppoint(), "initiator"))
        {
            System.out.println("APPOINT APPOINT INITIATOR");
            URN initiator = mpe.getInitiator();
            mpe.setCurrentactor(initiator);
            mpe.saveCurrentactor(initiator);
        }
        // system
        if (stage.isHumantask() && !stage.isProcessAsStage() && Objects.equals(stage.getAppoint(), "system"))
        {
            System.out.println("APPOINT APPOINT SYSTEM");
            try {
                URN systemAI = new URN("urn:Actor:AI:System:1");
                mpe.setCurrentactor(systemAI);
                mpe.saveCurrentactor(systemAI);
            } catch (URNExceptions.IncorrectFormat incorrectFormat) {
                incorrectFormat.printStackTrace();
            }
        }

        // TODO определено в документе, а не внешним гейтом конфигурации действующего лица


        // CALL IN

        stage.processIn(mpe); //this.inGateDynamicInstance.process(mpe);


        // JOURNAL

        Journaling.record(mpe, mpe.getSubject(), mpe.getCurrentstage(), mpe.getCurrentactor(), Journaling.Direction.IN, null);


        // if automated PROCESSING

        if (stage.isAutomated()) {
            System.out.println("AUTOMATED PROCESS()");
            stage.processingGateDynamicInstance.process(mpe);
            try {
                mpe.completeCurrentStage(); // act as human, try to complete stage after processing work done
            } catch (Exception e) {
                e.printStackTrace();
            }
        }

        // if PROCESS AS STAGE DELEGATE CALL

        if (stage.isProcessAsStage()) {
            System.out.println("~~~~~~~~~~~~~~~~~~~~~~~~~~~ >>> PROCESS AS STAGE " + stage.getCallProcessPrototype());
            try {
                mpe.setCurrentactor(null); // ?
                mpe.saveCurrentactor(null); // ?
                URN actor = mpe.getInitiator(); // pass initiator
                UPN upn = ManagedProcessesCentral.getInstance().startProcess(stage.getCallProcessPrototype(), actor, mpe, null, mpe.getSubject()); // + return control to
                ManagedProcessExecution mpeChild = ManagedProcessExecution.load(upn);
                if (mpeChild.getSubject() == null) {
                    // default subject is parent subject. redefine in first stage IN later
                    //mpeChild.setSubject(mpe.getSubject());
                    //mpeChild.saveSubject(mpe.getSubject());
                    System.out.println("~ NULL NULL NULL NULL NULL NULL NULL SUBJECT");
                }
                // set child id
                mpe.setMetadataKeyValue("child", upn.toString());
                // set child parent
                mpeChild.setMetadataKeyValue("parent", mpe.getUPN().toString());
                if (mpe.getInitiator() != null) mpeChild.setMetadataKeyValue("initiatorofparent", mpe.getInitiator().toString());
            } catch (Exception e) {
                e.printStackTrace();
            }
        }

        // TODO next?
        // ?????????????????????????????????????????????
    }

    private void transitionFromTo(Stage stageFrom, Stage stageTo) throws Exception {
        // stageFrom?
        this.setCurrentstage(stageTo.getName());
        this.saveCurrentstage(stageTo.getName());
        this.setCurrentactor(null);
        this.saveCurrentactor(null);
        // and begin current stage
        this.beginCurrentStage();
    }

    private void completeProcess() throws SQLException {

        System.out.println("/// ============================= PROCESS DONE " + this.upn);

        this.setDone(true);
        this.setCurrentstage(null);
        this.saveCurrentstage(null);
        this.setCurrentactor(null);
        this.saveCurrentactor(null);

       try{
       // try (Connection conn = ConnectionManager.getConnection()) {
         Connection conn = ConnectionManager.getConnectionForThread();

            try (PreparedStatement updateST = conn.prepareStatement("UPDATE \"ManagedProcess_Execution_Record\" SET done = true, currentstage = NULL, currentactor = NULL, nextstage = NULL WHERE id = ?")) {
                updateST.setLong(1, this.upn.getId());
                updateST.executeUpdate();
                conn.commit();
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
        }
        finally {

       }
        // close ticket
        Ticket.closeAllFor(this.getUPN());

        // complete parent process stage
        UPN returntopmeUPN = this.getReturntopme();
        if (returntopmeUPN != null)
        {
            System.out.println("\t{{ LETS COMPLETE PARENT PROCESS STAGE " + returntopmeUPN);
            ManagedProcessExecution returntopme = ManagedProcessExecution.load(returntopmeUPN);
            try {
                System.out.println("\t----------------------------------- returntopme.completeCurrentStage()");
                returntopme.completeCurrentStage();
            } catch (Exception e) {
                e.printStackTrace();
            }
            System.out.println("\t}} PARENT PROCESS STAGE DONE " + returntopmeUPN + ", ITS CUR STAGE " + returntopme.getCurrentstage());
            returntopme = ManagedProcessExecution.load(returntopmeUPN);
            System.out.println("\t}} AFTER RELOAD " + returntopmeUPN + ", ITS CUR STAGE " + returntopme.getCurrentstage());
        }
    }

    // TODO call Process as Stage
    public void initProcessAsStageBy(String processName, String asLocalStage, Integer userId)
    {

    }

    // * ManagedProcessExecution.completeCurrentStageBy(userId) = MPC.getProto(d,c,t) get current stage from proto, check role access
    // * ManagedProcessExecution.setStageMetaData('disApproveReason', 'richTxt') - вызывается при завершения этапа
    //   ManagedProcessExecution.completeStage('StartStage') = call current stage, actor; call out (1), PM.transition current state (2), call next(will be current) stage in (3)
    //      if stage is auto, call process after stage in, then recursive call .completeStage()
    //   managedProcessI.transitionFromToBy('StartStage', 'EditStage', userId)
    //      managedProcessI.initProcessAsStageBy('DMS', 'Approve', curStageActor) = stage container, init p, set new pmi.returntopmi = self pmi
    //      managedProcessI.completeProcess() = check delegate need
    //public void setCurrentStageMetaData(String key, String value) {}


    public ManagedProcessMastercopy getProcessMastercopy() {
        return mpMastercopy;
    }

    // Metadata
    public void setMetadataKeyValue(String key, String value) throws IllegalArgumentException {
        if (key == null) throw new IllegalArgumentException("setMetadataKeyValue key is null");
        if (value == null)
        {
            StackTraceElement[] stackTraceElements = Thread.currentThread().getStackTrace();
            //System.out.println(stackTraceElements[0].getClassName() + " " + stackTraceElements[0].getMethodName() + " " + stackTraceElements[0].getFileName() + " " + stackTraceElements[0].getLineNumber()); //
            if (stackTraceElements[1].getMethodName() != "saveSubject") System.out.println("@ " + stackTraceElements[1].getClassName() + " " + stackTraceElements[1].getMethodName() + " " + stackTraceElements[1].getFileName() + " " + stackTraceElements[1].getLineNumber()); //
            System.out.println("@ " + stackTraceElements[2].getClassName() + " " + stackTraceElements[2].getMethodName() + " " + stackTraceElements[2].getFileName() + " " + stackTraceElements[2].getLineNumber()); //
            if (stackTraceElements[3] != null) System.out.println("@ " + stackTraceElements[3].getClassName() + " " + stackTraceElements[3].getMethodName() + " " + stackTraceElements[3].getFileName() + " " + stackTraceElements[3].getLineNumber()); //
            if (stackTraceElements[4] != null) System.out.println("@ " + stackTraceElements[4].getClassName() + " " + stackTraceElements[4].getMethodName() + " " + stackTraceElements[4].getFileName() + " " + stackTraceElements[4].getLineNumber()); //
            if (stackTraceElements[5] != null) System.out.println("@ " + stackTraceElements[5].getClassName() + " " + stackTraceElements[5].getMethodName() + " " + stackTraceElements[5].getFileName() + " " + stackTraceElements[5].getLineNumber()); //
            if (stackTraceElements[6] != null) System.out.println("@ " + stackTraceElements[6].getClassName() + " " + stackTraceElements[6].getMethodName() + " " + stackTraceElements[6].getFileName() + " " + stackTraceElements[6].getLineNumber()); //
            if (stackTraceElements[7] != null) System.out.println("@ " + stackTraceElements[7].getClassName() + " " + stackTraceElements[7].getMethodName() + " " + stackTraceElements[7].getFileName() + " " + stackTraceElements[7].getLineNumber()); //
            if (stackTraceElements[8] != null) System.out.println("@ " + stackTraceElements[8].getClassName() + " " + stackTraceElements[8].getMethodName() + " " + stackTraceElements[8].getFileName() + " " + stackTraceElements[8].getLineNumber()); //
            if (stackTraceElements[9] != null) System.out.println("@ " + stackTraceElements[9].getClassName() + " " + stackTraceElements[9].getMethodName() + " " + stackTraceElements[9].getFileName() + " " + stackTraceElements[9].getLineNumber()); //

            throw new IllegalArgumentException("setMetadataKeyValue key " + key + " value is null");
        }
        this.metadata.put(key, value);
        try {
            this.saveStageMetadata();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    public String getMetadataValueByKey(String key) {
        return this.metadata.get(key);
    }

    private void setMetadata(String metadata) {
        JsonReader reader = Json.createReader(new StringReader(metadata));
        JsonObject personObject = reader.readObject();
        reader.close();
        for (String key : personObject.keySet())
        {
            this.metadata.put(key, personObject.getString(key));
        }
    }

    private void saveStageMetadata() throws SQLException {
        String jsonString = null;
        JsonObjectBuilder ob = Json.createObjectBuilder();
        //this.metadata.forEach((k, v) -> System.out.println(k + ":::" + v));
        this.metadata.forEach((k, v) -> ob.add(k, v)); // beware of null values (now checked in set meta)
        JsonObject JSON = ob.build();
        StringWriter stringWriter = new StringWriter();
        JsonWriter writer = Json.createWriter(stringWriter);
        writer.writeObject(JSON);
        writer.close();
        jsonString = stringWriter.getBuffer().toString();
        try{
     //   try (Connection conn = ConnectionManager.getConnection()) {
           Connection conn = ConnectionManager.getConnectionForThread();
            try (PreparedStatement updateST = conn.prepareStatement("UPDATE \"ManagedProcess_Execution_Record\" SET metadata = ? WHERE id = ?")) {
                PGobject jsonObject = new PGobject();
                jsonObject.setType("json");
                jsonObject.setValue(jsonString);
                updateST.setObject(1, jsonObject);
                updateST.setLong(2, this.upn.getId());
                int i = updateST.executeUpdate();
                conn.commit();
                //System.out.println(">>> updated metadata " + i + updateST.toString()); //
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
        }
        finally {

        }
    }

    public URN getSubject() {
        return subject;
    }

    public void setSubject(URN subject) throws SQLException {
        System.out.println("++ setSubject " + subject); //
        this.subject = subject;
    }

    public void saveSubject(URN subject) throws SQLException {
        System.out.println("++ saveSubject " + subject); //
        /*
        StackTraceElement[] stackTraceElements = Thread.currentThread().getStackTrace();
        //System.out.println(stackTraceElements[0].getClassName() + " " + stackTraceElements[0].getMethodName() + " " + stackTraceElements[0].getFileName() + " " + stackTraceElements[0].getLineNumber()); //
        if (stackTraceElements[1].getMethodName() != "saveSubject") System.out.println("@ " + stackTraceElements[1].getClassName() + " " + stackTraceElements[1].getMethodName() + " " + stackTraceElements[1].getFileName() + " " + stackTraceElements[1].getLineNumber()); //
        System.out.println("@ " + stackTraceElements[2].getClassName() + " " + stackTraceElements[2].getMethodName() + " " + stackTraceElements[2].getFileName() + " " + stackTraceElements[2].getLineNumber()); //
        if (stackTraceElements[3] != null) System.out.println("@ " + stackTraceElements[3].getClassName() + " " + stackTraceElements[3].getMethodName() + " " + stackTraceElements[3].getFileName() + " " + stackTraceElements[3].getLineNumber()); //
        if (stackTraceElements[4] != null) System.out.println("@ " + stackTraceElements[4].getClassName() + " " + stackTraceElements[4].getMethodName() + " " + stackTraceElements[4].getFileName() + " " + stackTraceElements[4].getLineNumber()); //
        if (stackTraceElements[5] != null) System.out.println("@ " + stackTraceElements[5].getClassName() + " " + stackTraceElements[5].getMethodName() + " " + stackTraceElements[5].getFileName() + " " + stackTraceElements[5].getLineNumber()); //
        if (stackTraceElements[6] != null) System.out.println("@ " + stackTraceElements[6].getClassName() + " " + stackTraceElements[6].getMethodName() + " " + stackTraceElements[6].getFileName() + " " + stackTraceElements[6].getLineNumber()); //
        if (stackTraceElements[7] != null) System.out.println("@ " + stackTraceElements[7].getClassName() + " " + stackTraceElements[7].getMethodName() + " " + stackTraceElements[7].getFileName() + " " + stackTraceElements[7].getLineNumber()); //
        if (stackTraceElements[8] != null) System.out.println("@ " + stackTraceElements[8].getClassName() + " " + stackTraceElements[8].getMethodName() + " " + stackTraceElements[8].getFileName() + " " + stackTraceElements[8].getLineNumber()); //
        if (stackTraceElements[9] != null) System.out.println("@ " + stackTraceElements[9].getClassName() + " " + stackTraceElements[9].getMethodName() + " " + stackTraceElements[9].getFileName() + " " + stackTraceElements[9].getLineNumber()); //
        */
        //

           try{
       // try (Connection conn = ConnectionManager.getConnection()) {
             Connection conn = ConnectionManager.getConnectionForThread();
            try (PreparedStatement updateST = conn.prepareStatement("UPDATE \"ManagedProcess_Execution_Record\" SET subject = ? WHERE id = ?")) {
                updateST.setString(1, subject.toString());
                updateST.setLong(2, this.upn.getId());
                int i = updateST.executeUpdate();
                conn.commit();
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
        }
           finally{ }
    }

    public UPN getReturntopme() {
        return returntopme;
    }

    public void setReturntopme(UPN returntopme) {
        System.out.println("+ setReturntopme " + returntopme); //
        this.returntopme = returntopme;
    }

    public void saveReturntopme(UPN returntopme) {
        System.out.println("++ saveReturntopme " + returntopme); //
        try {
            try{
          //  try (Connection conn = ConnectionManager.getConnection()) {
                 Connection conn = ConnectionManager.getConnectionForThread();
                try (PreparedStatement updateST = conn.prepareStatement("UPDATE \"ManagedProcess_Execution_Record\" SET returntopme = ? WHERE id = ?")) {
                    if (nextstage == null)
                        updateST.setNull(1, java.sql.Types.VARCHAR);
                    else
                        updateST.setString(1, returntopme.toString());
                    updateST.setLong(2, this.upn.getId());
                    int i = updateST.executeUpdate();
                    conn.commit();
                } catch (SQLException e) {
                    conn.rollback();
                    e.printStackTrace(); // + Log error
                }
            }
            finally {

            }
        } catch (SQLException e) {
            e.printStackTrace(); // + Log error
        }
    }

    public String getCurrentstage() {
        return currentstage;
    }

    private void setCurrentstage(String currentstage) {
        System.out.println("+ setCurrentstage " + currentstage); //
        this.currentstage = currentstage;
    }

    private void saveCurrentstage(String currentstage) throws SQLException {
        System.out.println("++ saveCurrentstage " + currentstage); //
        try{
     //   try (Connection conn = ConnectionManager.getConnection()) {
               Connection conn = ConnectionManager.getConnectionForThread();
            try (PreparedStatement updateST = conn.prepareStatement("UPDATE \"ManagedProcess_Execution_Record\" SET currentstage = ? WHERE id = ?")) {
                if (currentstage == null)
                    updateST.setNull(1, java.sql.Types.VARCHAR);
                else
                    updateST.setString(1, currentstage);
                updateST.setLong(2, this.upn.getId());
                int i = updateST.executeUpdate();
                conn.commit();
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
        } finally {

        }
    }

    public String getNextstage() {
        return nextstage;
    }

    // TODO in outs its setted but not saved
    public void setNextstage(String nextstage) throws ManagedProcessException.StageNotExists {
        System.out.println("+ setNextstage " + (nextstage == null ? "NULL" : nextstage)); //
        if (nextstage == null)
        {
            System.out.println("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"); //
            this.nextstage = nextstage;
        }
        else {
            boolean exists = this.getProcessMastercopy().stages.containsKey(nextstage);
            if (exists)
                this.nextstage = nextstage;
            else
                throw new ManagedProcessException.StageNotExists(nextstage);
        }
    }

    // Exception bounds
    public void saveNextstage(String nextstage) {
        System.out.println("++ saveNextstage " + (nextstage == null ? "saveNextstage" : nextstage)); //
        try {

            try{
            //try (Connection conn = ConnectionManager.getConnection()) {
                   Connection conn = ConnectionManager.getConnectionForThread();
                try (PreparedStatement updateST = conn.prepareStatement("UPDATE \"ManagedProcess_Execution_Record\" SET nextstage = ? WHERE id = ?")) {
                    if (nextstage == null)
                        updateST.setNull(1, java.sql.Types.VARCHAR);
                    else
                        updateST.setString(1, nextstage);
                    updateST.setLong(2, this.upn.getId());
                    int i = updateST.executeUpdate();
                    conn.commit();
                } catch (SQLException e) {
                    conn.rollback();
                    e.printStackTrace(); // + Log error
                }
            } finally {

            }
        } catch (SQLException e) {
            e.printStackTrace(); // + Log error
        }
    }


    public URN getCurrentactor() {
        return currentactor;
    }

    public void setCurrentactor(URN currentactor) {
        this.currentactor = currentactor;
    }

    // Exception bounds
    public void saveCurrentactor(URN currentactor) {
            try {
            //try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();

                try (PreparedStatement updateST = conn.prepareStatement("UPDATE \"ManagedProcess_Execution_Record\" SET currentactor = ? WHERE id = ?")) {
                    if (currentactor == null)
                        updateST.setNull(1, Types.VARCHAR);
                    else
                        updateST.setString(1, currentactor.toString());
                    updateST.setLong(2, this.upn.getId());
                    updateST.executeUpdate();
                    //conn.commit();
                } catch (SQLException e) {
                   // conn.rollback();
                    e.printStackTrace(); // + Log error
                }
                //
                if (currentactor != null && currentactor.toString().contains("Management:Post:Individual")) { // пропускаем для Actor:System и тп
                    //
                    try (
                            Statement stat = conn.createStatement();
                            ResultSet rs = stat.executeQuery("SELECT id FROM \"Feed_MPETicket_InboxItem\" WHERE \"ManagementPostIndividual\" = "+currentactor.getId()+" AND \"ManagedProcessExecutionRecord\" = "+this.getUPN().getId()+" ");
                    ) {
                        // активация тикета, созданного ранее (актер уже был в процессе на прошлых шагах или мы вернулись к прошлым шагам)
                        if (rs.next()) {
                            Long tickedId = rs.getLong("id");
                            boolean done = Ticket.activateAllRightsFor(tickedId);
                            System.out.println("Ticket Activation " + done + " " + tickedId);
                        }
                        else
                        {
                            // создание нового тикета
                            // Ticket.createWithoutRightsForActorProcess(currentactor, this.getUPN());
                            Ticket.createAndActivateAllRightsForActorProcess(currentactor, this.getUPN());
                            System.out.println("Tickets Creation");
                        }
                      //  conn.commit();
                    } catch (SQLException e) {
                        e.printStackTrace();
                        conn.rollback();
                        throw e;
                    }
                }
                //
                conn.commit();
            //}
        } catch (SQLException e) {
            e.printStackTrace(); // + Log error
        }
    }

    public boolean isDone() {
        return done;
    }

    private void setDone(boolean done) {
        if (done == true)
            System.out.println("+ setDone + sql setted in this.completeProcess()"); //
        this.done = done;
        // sql setted in this.completeProcess()
    }

    public UPN getUPN() {
        return upn;
    }



    public static int debugAll() {
        int totalrows = 0;
        System.out.println("--= DEBUG ALL =--"); //
        try {
        //try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();

            try (
                    Statement stat = conn.createStatement();
                    ResultSet rs = stat.executeQuery("SELECT id, prototype, subject, metadata, currentstage, currentactor, returntopme, done, nextstage, initiator FROM \"ManagedProcess_Execution_Record\" ORDER BY created DESC");
            ) {
                while (rs.next()) {
                    System.out.println("DEBUG ::::: " + rs.getLong("id") + " d:" +  rs.getBoolean("done") + " cs:" + rs.getString("currentstage") + " ca:" + rs.getString("currentactor") + " proto:" + rs.getString("prototype") + "\t s:" +  rs.getString("subject") + "\t " + rs.getString("metadata")  + " ns:" + rs.getString("nextstage") + " ini:" + rs.getString("initiator") );
                    totalrows++;
                }
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
            conn.commit(); // after result set is closed
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return totalrows;
    }

    public static void clearAll() {

        System.out.println("--= CLEAR ALL =--"); //
        try {
       // try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();

            try (PreparedStatement updateST = conn.prepareStatement("DELETE FROM \"ManagedProcess_Execution_Record\"")) {
                int i = updateST.executeUpdate();
              //  conn.commit();
            } catch (SQLException e) {
              //  conn.rollback();
                throw e;
            }

            try (PreparedStatement updateST = conn.prepareStatement("DELETE FROM \"ManagedProcess_Journal_Record\"")) {
                int i = updateST.executeUpdate();
              //  conn.commit();
            } catch (SQLException e) {
              //  conn.rollback();
                throw e;
            }

            try (PreparedStatement updateST = conn.prepareStatement("DELETE FROM \"Feed_MPETicket_InboxItem\"")) {
                int i = updateST.executeUpdate();
             //   conn.commit();
            } catch (SQLException e) {
              //  conn.rollback();
                throw e;
            }

            try (PreparedStatement updateST = conn.prepareStatement("DELETE FROM \"DMS_DecisionSheet_Signed\"")) {
                int i = updateST.executeUpdate();
              //  conn.commit();
            } catch (SQLException e) {
              //  conn.rollback();
                throw e;
            }

            try (PreparedStatement updateST = conn.prepareStatement("DELETE FROM \"DMS_Document_Universal\"")) {
                int i = updateST.executeUpdate();
                conn.commit();
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }

            try (PreparedStatement updateST = conn.prepareStatement("DELETE FROM \"DMS_Copy_Controled\"")) {
                int i = updateST.executeUpdate();
                conn.commit();
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }

            try (PreparedStatement updateST = conn.prepareStatement("DELETE FROM \"Directory_UKDState_IssueRecord\"")) {
                int i = updateST.executeUpdate();
                conn.commit();
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }



        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    public URN getInitiator() {
        return initiator;
    }

    public void setInitiator(URN initiator) {
        this.initiator = initiator;
    }
}
