package digital.erp.process;

import digital.erp.symbol.Prototype;

import java.util.HashMap;
import java.util.Map;

public class ManagedProcessMastercopy {

    public Prototype prototype;
    public Map<String, Stage> stages;

    protected ManagedProcessMastercopy(Prototype prototype)
    {
        this.prototype = prototype;
        this.stages = new HashMap<>();
    }

    public String toString()
    {
        if (prototype == null) return "MPP NULL PROTOTYPE";
        if (stages == null) return "MPP " + prototype.toString() + " NULL STAGES";
        if (stages.size() == 0) return "MPP " + prototype.toString() + " EMPTY STAGES";
        return "PROTO " + prototype.toString() + " " + stages.toString();
    }

    public Stage getFirstStage()
    {
        for (Map.Entry<String, Stage> kv : this.stages.entrySet()) {
            if (kv.getValue().isFirst()) return kv.getValue();
        }
        return null;
    }

    /*
    //private static ManagedProcessMastercopy instance;
    public static ManagedProcessMastercopy getInstance() throws Exception
    {
        if (instance == null) throw new Exception("MPP Instance not initialized");
        return instance;
    }

    public static void initInstance(UPN upn) throws Exception
    {
        if (instance != null) throw new Exception("MPP Instance already initialized");
        // sql load
        instance = new ManagedProcessMastercopy();
    }
    */

}