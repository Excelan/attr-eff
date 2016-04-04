package digital.erp.symbol;

import java.util.Random;

public class UPN {

    private Prototype prototype;
    private Long id;

    public Prototype getPrototype() {
        return prototype;
    }

    public void setPrototype(Prototype prototype) {
        this.prototype = prototype;
    }

    public Long getId() {
        return id;
    }

    public void setId(Long id) {
        this.id = id;
    }

    public UPN(Prototype prototype, Long id) {
        this.prototype = prototype;
        this.id = id;
    }

    public UPN(Prototype prototype) {
        this.prototype = prototype;
        this.id = randomLong();
    }

    public UPN(String upn) throws UPNException.IncorrectFormat {
        String[] urna = upn.split(":");
        if (urna.length == 5) {
            this.prototype = new Prototype(urna[1], urna[2], urna[3]);
            this.id = Long.parseLong(urna[4]);
        } else
            throw new UPNException.IncorrectFormat(upn);
    }

    public String toString() {
        return "UPN:" + this.prototype.getInDomain() + ":" + this.prototype.getOfClass() + ":" + this.prototype.getOfType() + ":" + this.id.toString();
    }

    private long randomLong() {
        long LOWER_RANGE = 1000;
        long UPPER_RANGE = 10000000L;
        Random random = new Random();
        long randomValue = LOWER_RANGE + (long) (random.nextDouble() * (UPPER_RANGE - LOWER_RANGE));
        return randomValue;
    }
}