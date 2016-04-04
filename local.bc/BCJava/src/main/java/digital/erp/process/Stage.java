package digital.erp.process;

import digital.erp.symbol.Prototype;
import digital.erp.symbol.UPN;
import digital.erp.symbol.URN;
import digital.erp.symbol.URNExceptions;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.Configuration;

import javax.json.JsonObject;
import java.lang.reflect.Constructor;
import java.lang.reflect.InvocationTargetException;
import java.util.Objects;

public class Stage {

    private Prototype processPrototype; // needed to build in/out class path
    private String name;
    private String appoint;
    private boolean humantask;
    private boolean automated;
    private boolean processAsStage;
    private Prototype callProcessPrototype;
    private StageIn inGateDynamicInstance;
    private StageOut outGateDynamicInstance;
    protected StageProcessing processingGateDynamicInstance;
    private String nextstage;
    private boolean first = false;
    private String classNameBase;
    private Integer timelimit = null;

    public Stage(Prototype processPrototype, String name) throws NullPointerException {
        if (name == null) throw new NullPointerException("Stage construct with null name");
        this.name = name;
        this.processPrototype = processPrototype;
    }

    public String toString() {
        return "STAGE: " + name + " humantask:" + humantask + " next:" + nextstage + " appoint: " + appoint;
    }

    public Integer getTimelimit() {
        return timelimit;
    }

    public void setTimelimit(Integer timelimit) {
        this.timelimit = timelimit;
    }

    protected void setHumantask(boolean humantask) {
        this.humantask = humantask;
    }


    protected void loadClasses()
    {
        // format "process.DMS.ClaimsManagement.Claim.EditingIn"
        this.classNameBase = "process." + processPrototype.getInDomain() + "." + processPrototype.getOfClass() + "." + processPrototype.getOfType() + "." + this.name;
        try {
            Class inGate = Class.forName(this.classNameBase + "In");
            Constructor inGateC = inGate.getConstructor();
            this.inGateDynamicInstance = (StageIn) inGateC.newInstance();
            Class outGate = Class.forName(this.classNameBase + "Out");
            Constructor outGateC = outGate.getConstructor();
            this.outGateDynamicInstance = (StageOut) outGateC.newInstance();
            System.out.println("LOADED: " + this.classNameBase + " In, Out " + this.getTimelimit());
        } catch (ClassNotFoundException | NoSuchMethodException | InstantiationException | InvocationTargetException | IllegalAccessException e) {
            System.err.println("ERROR: " + e.getClass() + " " + e.getMessage());
        }
        if (this.isAutomated()) {
            try {
                Class processingGate = Class.forName(this.classNameBase + "Processing");
                Constructor processingGateC = processingGate.getConstructor();
                this.processingGateDynamicInstance = (StageProcessing) processingGateC.newInstance();
                System.out.println("LOADED: " + this.classNameBase + " Processing");
            } catch (ClassNotFoundException | NoSuchMethodException | InstantiationException | InvocationTargetException | IllegalAccessException e) {
                System.err.println("ERROR: " + e.getClass() + " " + e.getMessage());
            }
        }
    }

    protected void processIn(ManagedProcessExecution mpe) {
        this.inGateDynamicInstance.process(mpe);
    }

    protected void processOut(ManagedProcessExecution mpe) {
        this.outGateDynamicInstance.process(mpe);
    }

    public boolean isHumantask() {
        return humantask;
    }

    public Prototype getProcessPrototype() {
        return processPrototype;
    }

    public String getName() {
        return name;
    }

    public String getNextstage() {
        return nextstage;
    }

    protected void setNextstage(String nextstage) {
        this.nextstage = nextstage;
    }

    public boolean isFirst() {
        return first;
    }

    protected void setFirst(boolean first) {
        this.first = first;
    }

    public boolean isProcessAsStage() {
        return processAsStage;
    }

    protected void setProcessAsStage(boolean processAsStage) {
        this.processAsStage = processAsStage;
    }

    public Prototype getCallProcessPrototype() {
        return callProcessPrototype;
    }

    protected void setCallProcessPrototype(Prototype callProcessPrototype) {
        this.callProcessPrototype = callProcessPrototype;
    }

    public boolean isAutomated() {
        return automated;
    }

    protected void setAutomated(boolean automated) {
        this.automated = automated;
    }

    public String getAppoint() {
        return appoint;
    }

    public void setAppoint(String appoint) {
        this.appoint = appoint;
    }
}
