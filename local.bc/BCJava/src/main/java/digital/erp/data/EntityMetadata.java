package digital.erp.data;

import digital.erp.symbol.URN;
import java.util.Date;

public class EntityMetadata {

    public URN urn;
    public String state;
    public URN initiator;
    public Date created;
    public Date updated;

    public EntityMetadata(URN urn, String state, URN initiator, Date created, Date updated)
    {
        this.urn = urn;
        this.state = state;
        this.initiator = initiator;
        this.created = created;
        this.updated = updated;
    }

    public String toString()
    {
        return "ENTITYMETADATA " + this.urn + " state:" + this.state + " initiator:" + this.initiator.toString() + " created:" + this.created.toString() + " updated:" + (this.updated != null ? this.updated.toString() : "");
    }

    // TODO setState

}
