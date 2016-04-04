import com.attracti.app.gates.GateExampleNs.{Response, GateExample, Request}
import digital.erp.domains.document.DocumentMetadata
import digital.erp.domains.document.Document
import digital.erp.symbol.URN
import erp.process._
import digital.erp.data._
import experiments.feature.XMLExample
import net.goldcut.database.ConnectionManager
import java.sql.{SQLException, DriverManager, Connection, ResultSet, Statement}

object GC extends App {

    //Screen
    //val c = new Claim()

//    println("========= JSON")
//    experiments.feature.JsonExample.run1()
//    experiments.feature.JsonExample.run2()

    println("========= DB")
    val connectionTry = db.PG.getConnection()
    connectionTry match {
        case Some(connection) =>

            db.PG.doit(connection)

//            println("========= GATE")
//            val g = new GateExample()
//            g.setConnection(connection)

            /*
            val reqJSON: String = "{\"requrn\": \"URN-JSON-111\", \"struct\": {\"isa\":\"A\", \"isb\":\"B\"}, \"structmult\": [ {\"im\":\"SMI1\"}, {\"im\":\"SMI2\"} ] }"
            println(reqJSON)
            val request: Request = Request.fromJSON(reqJSON)
            // val res = g.process(new Request("URN-USER-123")) // manual

            val resp: Response = g.process(request)

            println(resp.i)
            println(resp.s)
            println(resp.toJSON)

            println("=========================================")

            val claimStart = new Start()
            */
//            db.PG.releaseConnection(connection)

//
            println("=========================================")

//            val urn = new URN("urn-dms-qualitycontrol-claim-123"); //

//            val createdURN = Entity.createDraftBy(urn, 77)
//            println(createdURN.toString)

//            Entity.directUpdateString(urn, "state", "editing")
            //Entity.directUpdateString(urn, "name", "NewCompany")

//            val emd : EntityMetadata = Entity.loadEntityMetadata(urn)
//            println(emd)

//            val emd2 : DocumentMetadata = Document.loadDocumentMetadata(urn)
//            println(emd2)

            //XMLExample.run()


        case None =>
            println("NO DB CONN")
    }


//    println("========= XML")
//    val x = xml.XMLimport.loadFileXML("src/main/resources/process/claim.xml")
//    (x \\ "process" \ "responsibility" \\ "stage").map {
//        s => println((s \ "@name").text)
//    }

    //experiments.server.HttpServer.run()

//    println("========= Local Class")
//    experiments.feature.LocalClassExample.run()



}
