package digital.erp.domains.document;

import digital.erp.data.Entity;
import digital.erp.data.EntityMetadata;
import digital.erp.symbol.UPN;
import digital.erp.symbol.URN;
import net.goldcut.database.ConnectionManager;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.util.Date;
import java.util.List;

public class DocumentMetadata extends EntityMetadata {

    private boolean privatedraft;
    private String code;
    private List<URN> authors;
    private boolean returned;
    private boolean vised;
    private List<URN> visapersons;
    private List<URN> visareceived;
    private boolean approved;
    private URN approvedby;
    private boolean done;
    private boolean archived;
    private UPN managedProcessExecution;
    private URN parent;
    private List<URN> children;
    private List<URN> related;

    public DocumentMetadata(URN urn, String code, String state, URN initiator, java.util.Date created, java.util.Date updated, boolean privatedraft, boolean returned, boolean done, boolean archived, boolean vised, boolean approved, UPN managedProcessExecution, URN parent, List<URN> children, List<URN> related) {
        super(urn, state, initiator, created, updated);
        this.privatedraft = privatedraft;
        this.code = code;
        this.vised = vised;
        this.approved = approved;
        this.managedProcessExecution = managedProcessExecution;
        this.parent = parent;
        this.children = children;
        this.related = related;
        this.returned = returned;
        this.done = done;
        this.archived = archived;
    }

    public String toString() {
        StringBuffer sb = new StringBuffer();
        if (this.privatedraft) sb.append("P");
        if (this.returned) sb.append("R");
        if (this.done) sb.append("D");
        if (this.archived) sb.append("a");
        if (this.vised) sb.append("V");
        if (this.approved) sb.append("A");
        return "DOCUMENTMETADATA " + this.code + " " + this.state + "/" + sb + " initiator:" + this.initiator.toString() + " parent:" + (this.parent != null ? this.parent.toString() : "NONE") + " children:" + this.children.size() + " related:" + this.related.size() + " created:" + this.created.toString(); // + " " + this.urn + " process:" + this.managedProcessExecution +
    }

    public boolean isVised() {
        return vised;
    }

    private void setVised(boolean vised) {
        this.vised = vised;
    }

    public boolean isApproved() {
        return approved;
    }

    private void setApproved(boolean approved) {
        this.approved = approved;
    }

    public UPN getManagedProcessExecution() {
        return managedProcessExecution;
    }

    private void setManagedProcessExecution(UPN managedProcessExecution) {
        this.managedProcessExecution = managedProcessExecution;
    }

    public URN getParent() {
        return parent;
    }

    public void setParent(URN parent) {
        this.parent = parent;
        try {
            Entity.directUpdateString(this.urn, "parent", parent.toString());
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public void makePublic() {
        try {
            System.out.println(this.urn + " PUBLIC NOW ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~");
            Entity.directUpdateBoolean(this.urn, "privatedraft", false);
            System.out.println(this.urn + " OK ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~");
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public void makeArchived() {
        try {
            Entity.directUpdateBoolean(this.urn, "archived", true);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public void makeVised() {
        try {
            Entity.directUpdateBoolean(this.urn, "vised", true);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public void makeApproved() {
        try {
            Entity.directUpdateBoolean(this.urn, "approved", true);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public void makeDone() {
        try {
            Entity.directUpdateBoolean(this.urn, "done", true);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public void addNeedsVisaFrom(URN additionalvisant) {
        try {
            Entity.directArrayAppendString(this.urn, "visapersons", additionalvisant.toString());
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public void removeNeedsVisaFrom(URN additionalvisant) {
        try {
            Entity.directArrayRemoveString(this.urn, "visapersons", additionalvisant.toString());
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public void addVisedBy(URN visedby) {
        try {
            Entity.directArrayAppendString(this.urn, "visareceived", visedby.toString());
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public void clearVisedAndAllVisaReceived() {
        try {
            Entity.directArrayClear(this.urn, "visareceived");
            Entity.directUpdateBoolean(this.urn, "vised", false);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public void addAuthor(URN author) {
        try {
            Entity.directArrayAppendString(this.urn, "authors", author.toString());
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public List<URN> getChildren() {
        return children;
    }

    public void setChildren(List<URN> children) {
        this.children = children;
    }

    public List<URN> getRelated() {
        return related;
    }

    public void setRelated(List<URN> related) {
        this.related = related;
    }

    public String getCode() {
        return code;
    }

}
