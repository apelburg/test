// JavaScript Document

function statusTooltip() { // Функция конструктор класса Tooltip
   this.tooltip = document.createElement("div"); // Создать div для тени
   this.tooltip.style.position = "absolute"; // Абсолютное позиционирование
   this.tooltip.style.visibility = "hidden"; // Изначально подсказка скрыта
   this.tooltip.className = "tooltipShadow"; // Определить его стиль
   this.content = document.createElement("div"); // Создать div с содержимым
   this.content.style.position = "relative"; // Относительное позиционирование
   this.content.className = "tooltipContent"; // Определить его стиль
   this.tooltip.appendChild(this.content); // Добавить содержимое к тени
}
// Определить содержимое, установить позицию окна с подсказкой и отобразить ее
statusTooltip.prototype.show = function(text, y , х) {
   // Добавить подсказку в документ, если это еще не сделано
   if (this.tooltip.parentNode != document.body) document.body.appendChild(this.tooltip);
   this.content.innerHTML = text; // Записать текст подсказки.
   this.tooltip.style.left = х - 150 + "px"; // Определить положение.
   this.tooltip.style.top = y - 15 + "px";
   this.tooltip.style.visibility = "visible"; // Сделать видимой.
  
};
// Скрыть подсказку
statusTooltip.prototype.hide = function() {
   this.tooltip.style.visibility = "hidden"; // Сделать невидимой.
};
statusTooltip.prototype.getY= function(element) {
   var y= 0;
   var х = 0;
   for(var e = element; e != null; e = e.offsetParent){ // Цикл по offsetParent
      y += e.offsetTop;
	  х += e.offsetLeft;
   }

   for(e = element.parentNode; e && e != document.body; e = e.parentNode){
      if(e.scrollTop) y -= e.scrollTop; 
   }
   return [y,х];
}

statusTooltip.prototype.schedule = function(target) {

   var text = target.getAttribute("tooltip");
   if (!text) return;
  
   var pos =  statusTooltip.prototype.getY(target.firstChild);
   var self = this; 
   self.show(text, pos[0], pos[1]);
   $(target).mouseleave(mouseout);
   function mouseout() {self.hide();  }
}
// Определить единственный глобальный объект Tooltip для общего пользования
statusTooltip.tooltip = new statusTooltip();

statusTooltip.schedule = function(target, e) {statusTooltip.tooltip.schedule(target, e); }
