package digital.erp.symbol;

import java.util.Random;

public class URN {

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

    public URN(Prototype prototype, Long id) {
        this.prototype = prototype;
        this.id = id;
    }

    public URN(Prototype prototype) {
        this.prototype = prototype;
        this.id = randomLong();
    }

    public URN(String urn) throws URNExceptions.IncorrectFormat {
        if (urn == null) throw new URNExceptions.IncorrectFormat(urn);
        String[] urna = urn.split(":");
        //if (urna[0].toUpperCase() != "URN")
        //    throw new URNExceptions.NotURN(urn);
        if (urna.length == 5) {
            this.prototype = new Prototype(urna[1], urna[2], urna[3]);
            this.id = Long.parseLong(urna[4]);
        } else
            throw new URNExceptions.IncorrectFormat(urn);
    }

    public String toString() {
        return "urn:" + this.prototype.getInDomain() + ":" + this.prototype.getOfClass() + ":" + this.prototype.getOfType() + ":" + this.id.toString();
    }

    public static long randomLong() {
        long LOWER_RANGE = 1000;
        long UPPER_RANGE = 1000000000L;
        Random random = new Random();
        long randomValue = LOWER_RANGE + (long) (random.nextDouble() * (UPPER_RANGE - LOWER_RANGE));
        return randomValue;
    }

    public static long randomInRange(long LOWER_RANGE, long UPPER_RANGE) {
        Random random = new Random();
        long randomValue = LOWER_RANGE + (long) (random.nextDouble() * (UPPER_RANGE - LOWER_RANGE));
        return randomValue;
    }

}
