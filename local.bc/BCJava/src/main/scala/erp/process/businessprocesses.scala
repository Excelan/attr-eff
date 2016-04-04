package erp.process

abstract class StageType {}

case class Auto() extends StageType
case class FormEntry() extends StageType
case class FormEdit() extends StageType
case class FormPreview() extends StageType

abstract class Stage {
    val nodetype:StageType // type Form Data Entry, Form Data Edit, Data Preview, Data Preview + Action(Complete, Visa, Approve, Done, Review)
    // forward, route, +return proto
    // RBAC access
    // access hints - client can
}
abstract class StageIn {}
abstract class StageOut {}

abstract class Transition {
    // (direct, decisions) completeNode match screenName
    // out/from business rules - условия, что изменить в документе
    // in/to business rules
    // RBAC access
    // завершить узел (и выполнить действия для перехода на следующий) from, to
}

abstract class BusinessProcess {
    val name:String
    // container document
    val nodes:Seq[Stage] // nodes (screen, actor role) case classes
    // transitions from node, to node
}

// данные процесса, данные этапа

// показать экран в процессе для определнной роли
// по документу получить его процесс и текущий этап.
// получить все документы на текущей стадии исполнения
// получить все инициированные собой документы
//

