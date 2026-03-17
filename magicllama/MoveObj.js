// Moving Objects Script
// By Virtual_Max
//
// Permission to use, copy, modify, and distribute this software and its documentation
// for NON-COMMERCIAL purposes and  without fee is hereby granted provided that this
// notice appears in all copies.
//
// VIRTUAL MAX MAKES NO REPRESENTATIONS OR WARRANTIES ABOUT THE SUITABILITY OF THE
// SOFTWARE, EITHER EXPRESS OR IMPLIED

var vmin = 3, vmax = 6;
var vr = 2;

function Chip(chipname, width, height)
{
	this.id = chipname;
	this.element = document.getElementById ? document.getElementById(chipname) : document.all[chipname];

	this.w = width;
	this.h = height;

	this.xx = parseInt(typeof(this.element.style.pixelTop) != "undefined" ? this.element.style.pixelTop : this.element.style.top);
	this.yy = parseInt(typeof(this.element.style.pixelLeft) != "undefined" ? this.element.style.pixelLeft : this.element.style.left);

	this.vx = vmin + vmax * Math.random();
	this.vy = vmin + vmax * Math.random();
	this.timer1 = null;

	this.stop = function ()
	{
		if (parseInt(navigator.appVersion.substring(0, 1)) < 4)
			return;

		if (this.timer1 != null)
			clearTimeout(this.timer1);
	}

	this.move = function ()
	{
		if (parseInt(navigator.appVersion.substring(0, 1)) < 4)
			return;

		if (typeof(window.pageXOffset) != "undefined")
		{
			pageX = window.pageXOffset-5;
			pageY = window.pageYOffset-5;

			pageW = window.innerWidth;
			pageH = window.innerHeight;
		}
		else
		{
			pageX = window.document.body.scrollLeft;
			pageY = window.document.body.scrollTop;

			pageW = window.document.body.offsetWidth-5;
			pageH = window.document.body.offsetHeight-5;
		}

		// Take care of the scroll bar...
		pageW -= 16;

		this.xx = this.xx + this.vx;
		this.yy = this.yy + this.vy;

		this.vx += vr * (Math.random() - 0.5);
		this.vy += vr * (Math.random() - 0.5);

		if (this.vx > (vmax + vmin))
			this.vx = (vmax + vmin) * 2 - this.vx;
		if (this.vx < (-vmax - vmin))
			this.vx = (-vmax - vmin) * 2 - this.vx;

		if (this.vy > (vmax + vmin))
			this.vy = (vmax + vmin) * 2 - this.vy;
		if (this.vy < (-vmax - vmin))
			this.vy = (-vmax - vmin) * 2 - this.vy;

		// Check left bound.
		if (this.xx <= pageX)
		{
			this.xx = pageX;
			this.vx = vmin + vmax * Math.random();
		}
		// Check right bound.
		if (this.xx >= pageX + pageW - this.w)
		{
			this.xx = pageX + pageW - this.w;
			this.vx = -vmin - vmax * Math.random();
		}

		// Check upper bound.
		if (this.yy <= pageY)
		{
			this.yy = pageY;
			this.vy = vmin + vmax * Math.random();
		}
		// Check bottom bound.
		if (this.yy >= pageY + pageH - this.h)
		{
			this.yy = pageY + pageH - this.h;
			this.vy = -vmin - vmax * Math.random();
		}

		if (document.getElementById)
		{
			this.element.style.left = this.xx + "px";
			this.element.style.top = this.yy + "px";
		}
		else if (document.all)
		{
			this.element.style.pixelLeft = this.xx;
			this.element.style.pixelTop = this.yy;
		}

		this.timer1 = setTimeout(this.id + ".move();", llama_speed);
	}
}