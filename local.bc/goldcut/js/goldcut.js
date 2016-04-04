// -- GC

function URN(urn) {
    this.urn = urn;
    this.entity = this.entity();
    this.uuid = this.uuid();
}
URN.prototype.entity = function () {
    return this.urn.split('-')[1];
}
URN.prototype.uuid = function () {
    return this.urn.split('-')[2];
}
URN.prototype.generate = function (entity) {
    return ['urn', entity, getRandomArbitary(100000, 999000)].join('-');
}
function getRandomArbitary(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}
