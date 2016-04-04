package digital.erp.symbol;

public class Prototype {

    private String inDomain;
    private String ofClass;
    private String ofType;

    public String getInDomain() {
        return inDomain;
    }

    public void setInDomain(String inDomain) {
        this.inDomain = inDomain;
    }

    public String getOfClass() {
        return ofClass;
    }

    public void setOfClass(String ofClass) {
        this.ofClass = ofClass;
    }

    public String getOfType() {
        return ofType;
    }

    public void setOfType(String ofType) {
        this.ofType = ofType;
    }

    public Prototype(String inDomain, String ofClass, String ofType)
    {
        this.inDomain = inDomain;
        this.ofClass = ofClass;
        this.ofType = ofType;
    }

    public String toString()
    {
        return this.inDomain + ":" + this.ofClass + ":" + this.ofType;
    }

    public String getAlias()
    {
        return this.inDomain + "_" + this.ofClass + "_" + this.ofType;
    }

    public String getShortName()
    {
        return this.inDomain + "" + this.ofClass + "" + this.ofType;
    }

    public static Prototype fromString(String protostring) throws PrototypeException.IncorrectFormat
    {
        String[] urna = protostring.split(":");
        if (urna.length == 3) {
            return new Prototype(urna[0], urna[1], urna[2]);
        }
        else
            throw new PrototypeException.IncorrectFormat(protostring);
    }
}
