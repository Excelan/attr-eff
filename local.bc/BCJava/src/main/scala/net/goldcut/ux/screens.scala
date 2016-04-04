package net.goldcut.ux

/** задачи
заявить форму, принять данные из формы
ничего не знает о процессе, в котором участвует
кнопки действия сначала сохраняют форму, а затем передают контроль процессу
форма представлена value gate request type, описывающим структуру сохрянемых данных
shared form - полная! форма в домене данных (Document, BO, User)
shared data preview - полные данные в режиме просмотра
partial data form entry/edit/preview особенности формы в рамках экрана-процесса - тогда нет смысла в shared form
in domain (shared)? in screen (specific)?
    data specific - partial from entity, extended with parent, children, relative data in one form
    rich selects, select options
in process vs shared screen? если одни данные участвсуют в разных процессах, то есть смысл
*/
abstract class Screen {
    // node type
    // nav, title
    // main MVC (by type)
    // widgets outlets
        // widgets MVC[]
    // RBAC
    // outlets for actions (Complete(Next, Approve) + Reject (return to some point))
        // actions - gates
}

/** задачи
MVC

*/
abstract class Widget {

}
