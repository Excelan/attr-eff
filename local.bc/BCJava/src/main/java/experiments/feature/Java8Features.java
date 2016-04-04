package experiments.feature;


// Java 8 has been influenced by Scala much more than how Scala has been influenced by Java (@mariofusco)
// Java является самой мощной промышленной платформой и развитие ее стандарта влияет практически на все крупные компании. Глупо ожидать тут гонки за «плюшками».
// Многие не понимают, что Java — это больше про JVM и платформу, а не про то, как ставить стрелочки — "->" или "=>". Лямбду хотели ввести еще в 7, но отказались — и правильно. На тот момент это был всего лишь синтаксический сахар на абстрактными классами
// сама идея Functional Interfaces хоть по началу и выглядит отталкивающе, но привыкнув понимаешь ее гениальность. Они не просто ввели лямбды, но сделали их совместимыми с огромным количеством уже существующих библиотек
// Java 8 даже до C# не дотянет по удобству и лаконичности. А уж со Scala ей не тягаться. Лямбды — это хорошо, но не все
// Стримы — это элементы ФП над последовательностями
// При работе с коллекциями в самом деле очень удобно написать personList.Where(p=>p.Age>31) вместо list.getStream().filter((Person f) -> f.getAge() > 21). Хотите параллельно — AsParallel() и тд. Но вектор развития тяготеет к C#/.Net. Тот же аналог using блока — «try-with-resources» в 1.7. Может в 1.9 async будет ;)
// У меня лично твёрдое убеждение, что качество кода, написанного на любом мэйнстримовом языке, мало кореллирует с синтаксическим сахаром, доступным в этом языке

/*
LINQ
var l2 =
    (
        for e1 in list1
        join e2 in list2 on new {e1.Field1, e1.Field2} equals new {e2.Field1, e2.Field2}
        where e1.IntField > 0
        select e2.Field3
    ).ToList()
 */

// http://habrahabr.ru/post/188850/
// http://www.slideshare.net/SimonRitter/javase8-55thingsv2-sritter
// https://leanpub.com/whatsnewinjava8/read#leanpub-auto-optional
// http://www.oracle.com/technetwork/articles/java/architect-lambdas-part1-2080972.html

// Streams
// list.getStream().filter((Person f) -> f.getAge() > 21)
// persons.getStream().filter(p -> p.getAge() > 21);
// http://www.drdobbs.com/jvm/lambdas-and-streams-in-java-8-libraries/240166818
// http://winterbe.com/posts/2014/07/31/java8-stream-tutorial-examples/
// http://www.golovachcourses.com/java-8-stream-api/
// http://www.oraclejavamagazine-digital.com/javamagazine_open/20140304?pg=51#pg51
// https://dzone.com/articles/understanding-java-8-streams-1
// http://radar.oreilly.com/2015/02/java-8-streams-api-and-parallelism.html


// Optionals
// http://www.oracle.com/technetwork/articles/java/java8-optional-2175753.html
// http://habrahabr.ru/post/225641/
// http://www.tutorialspoint.com/java8/java8_optional_class.htm
// http://blog.jhades.org/java-8-how-to-use-optional/
// http://stackoverflow.com/questions/30864583/java-8-difference-between-optional-flatmap-and-optional-map
// https://dzone.com/articles/java-8-optional-whats-point
// http://blog.joda.org/2015/08/java-se-8-optional-pragmatic-approach.html

// java time (no more jodatime)
// Nashorn
// Вообще у Oracle есть своя реализация Node.js, креативно названная Node.jar. В чем уверено большинство людей, так это в том, что они хотят запускать всякие штуки на JVM, но не хотят использовать для этого синтаксис Java.
// Atomics, Accumulators
// SSL SNI

public class Java8Features {

}
