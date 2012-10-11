<?php
/**
 * Copyright (C) 2012 Graham Breach
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * For more information, please contact <graham@goat1000.com>
 */

class SVGGraphJavascript {

  private $settings;
  private $graph;
  protected $functions = array();
  protected $variables = array();
  protected $comments = array();
  protected $onload = FALSE;

  /**
   * Constructor takes array of settings and graph instance as arguments
   */
  public function __construct(&$settings, &$graph)
  {
    $this->settings = $settings;
    $this->graph = $graph;
  }

  /**
   * Return the settings as properties
   */
  public function __get($name)
  {
    if(isset($this->settings[$name]))
      return $this->settings[$name];

    return NULL;
  }

  /**
   * Adds a javascript function
   */
  public function AddFunction($name)
  {
    if(isset($this->functions[$name]))
      return TRUE;

    $simple_functions = array(
      'setattr' => "function setattr(i,a,v){i.setAttributeNS(null,a,v)}\n",
      'getE' => "function getE(i){return document.getElementById(i)}\n",
      'newtext' => "function newtext(c){return document.createTextNode(c)}\n",
    );

    if(isset($simple_functions[$name])) {
      $this->InsertFunction($name, $simple_functions[$name]);
      return;
    }

    switch($name)
    {
    case 'textFit' :
      $this->AddFunction('setattr');
      $fn = <<<JAVASCRIPT
function textFit(evt,x,y,w,h) {
  var t = evt.target;
  var aw = t.getBBox().width;
  var ah = t.getBBox().height;
  var trans = '';
  var s = 1.0;
  if(aw > w)
    s = w / aw;
  if(s * ah > h)
    s = h / ah;
  if(s != 1.0)
    trans = 'scale(' + s + ') ';
  trans += 'translate(' + (x / s) + ',' + ((y + h) / s) +  ')';
  setattr(t, 'transform', trans);
}\n
JAVASCRIPT;
      break;

    // fadeIn, fadeOut are shortcuts to fader function
    case 'fadeIn' : $name = 'fader';
    case 'fadeOut' : $name = 'fader';
    case 'fader' :
      $this->AddFunction('getE');
      $this->InsertVariable('faders', '', 1); // insert empty object
      $this->InsertVariable('fader_itimer', NULL);
      $fn = <<<JAVASCRIPT
function fadeIn(e,i,s){fader(e,i,0,1,s)}
function fadeOut(e,i,s){fader(e,i,1,0,s)}
function fader(e,i,o1,o2,s) {
  faders[i] = { id: i, o_start: o1, o_end: o2, step: (o1 < o2 ? s : -s) };
  fader_itimer || (fader_itimer = setInterval(fade,50));
}
function fade() {
  var f,ff,t,o;
  for(f in faders) {
    ff = faders[f], t = getE(ff.id);
    o = (t.style.opacity == '' ? ff.o_start : t.style.opacity * 1);
    o += ff.step;
    t.style.opacity = o < .01 ? 0 : (o > .99 ? 1 : o);
    if((ff.step > 0 && o >= 1) || (ff.step < 0 && o <= 0))
      delete faders[f];
  }
}\n
JAVASCRIPT;
      break;

    case 'newel' :
      $this->AddFunction('setattr');
      $fn = <<<JAVASCRIPT
function newel(e,a){
  var ns='http://www.w3.org/2000/svg', ne=document.createElementNS(ns,e),i;
  for(i in a)
    setattr(ne, i, a[i]);
  return ne;
}\n
JAVASCRIPT;
      break;
    case 'showhide' :
      $this->AddFunction('setattr');
      $fn = <<<JAVASCRIPT
function showhide(e,h){setattr(e,'visibility',h?'visible':'hidden');}\n
JAVASCRIPT;
      break;
    case 'finditem' :
      $fn = <<<JAVASCRIPT
function finditem(e,list) {
  var l = e.target.correspondingUseElement || e.target, t;
  while(!t && l.parentNode) {
    t = l.id && list[l.id]
    l = l.parentNode;
  }
  return t;
}\n
JAVASCRIPT;
      break;
    case 'tooltip' :
      $this->AddFunction('getE');
      $this->AddFunction('setattr');
      $this->AddFunction('newel');
      $this->AddFunction('showhide');
      $this->AddFunction('svgCoords');
      $this->InsertVariable('tooltipOn', '');
      if($this->tooltip_shadow_opacity) {
        $ttoffs = (2 - $this->tooltip_stroke_width/2) . 'px';
        $shadow = <<<JAVASCRIPT
    shadow = newel('rect',{
      fill: 'rgba(0,0,0,{$this->tooltip_shadow_opacity})',
      x:'{$ttoffs}',y:'{$ttoffs}',
      width:'10px',height:'10px',
      id: 'ttshdw',
      rx:'{$this->tooltip_round}px',ry:'{$this->tooltip_round}px'
    });
    tt.appendChild(shadow);
JAVASCRIPT;
      } else {
        $shadow = '';
      }
      $dpad = 2 * $this->tooltip_padding;
      $fn = <<<JAVASCRIPT
function tooltip(e,callback,on,param) {
  var tt = getE('tooltip'), rect = getE('ttrect'), shadow = getE('ttshdw'),
    offset = {$this->tooltip_offset},
    x = e.clientX + offset, y = e.clientY + offset, inner, brect, bw, bh,
    sw, sh, pos = svgCoords(e),
    de = e.target.correspondingUseElement || e.target;
  while(de.parentNode && de.nodeName != 'svg')
    de = de.parentNode;
  if(on && !tt) {
    tt = newel('g',{id:'tooltip',visibility:'visible'});
    rect = newel('rect',{
      stroke: '{$this->tooltip_colour}',
      'stroke-width': '{$this->tooltip_stroke_width}px',
      fill: '{$this->tooltip_back_colour}',
      width:'10px',height:'10px',
      id: 'ttrect',
      rx:'{$this->tooltip_round}px',ry:'{$this->tooltip_round}px'
    });
{$shadow}
    tt.appendChild(rect);
  }
  if(tt) {
    if(on) {
      if(tt.parentNode && tt.parentNode != de)
        tt.parentNode.removeChild(tt);
      x -= pos[0];
      y -= pos[1];
      de.appendChild(tt);
    }
    showhide(tt,on);
  }
  inner = callback(e,tt,on,param);
  if(inner && on) {
    brect = inner.getBBox();
    bw = Math.ceil(brect.width + {$dpad});
    bh = Math.ceil(brect.height + {$dpad});
    setattr(rect, 'width', bw + 'px');
    setattr(rect, 'height', bh + 'px');
    if(shadow) {
      setattr(shadow, 'width', (bw + {$this->tooltip_stroke_width}) + 'px');
      setattr(shadow, 'height', (bh + {$this->tooltip_stroke_width}) + 'px');
    }
    if(de.width) {
      sw = de.width.baseVal.value;
      sh = de.height.baseVal.value;
    } else {
      sw = window.innerWidth;
      sh = window.innerHeight;
    }
    if(bw + x > sw)
      x = Math.max(e.clientX - offset - bw,0);
    if(bh + y > sh)
      y = Math.max(e.clientY - offset - bh,0);
  }
  on && setattr(tt,'transform','translate('+x+' '+y+')');
  tooltipOn = on ? 1 : 0;
}\n
JAVASCRIPT;
      break;

    case 'texttt' :
      $this->AddFunction('getE');
      $this->AddFunction('setattr');
      $this->AddFunction('newel');
      $this->AddFunction('newtext');
      $tty = ($this->tooltip_font_size + $this->tooltip_padding) . 'px';
      $fn = <<<JAVASCRIPT
function texttt(e,tt,on,t){
  var ttt = getE('tooltiptext');
  if(on) {
    if(!ttt) {
      ttt = newel('text', {
        id: 'tooltiptext',
        fill: '{$this->tooltip_colour}',
        'font-size': '{$this->tooltip_font_size}px',
        'font-family': '{$this->tooltip_font}',
        'font-weight': '{$this->tooltip_font_weight}',
        x:'{$this->tooltip_padding}px',y:'{$tty}'
      });
      ttt.appendChild(newtext(t));
      tt.appendChild(ttt);
    } else {
      ttt.firstChild.data = t;
    }
  }
  ttt && showhide(ttt,on);
  return ttt;
}\n
JAVASCRIPT;
      break;
    case 'ttEvent' :
      $this->AddFunction('finditem');
      $this->AddFunction('init');
      $this->InsertVariable('initfns', NULL, 'ttEvt');
      $fn = <<<JAVASCRIPT
function ttEvt() {
  document.addEventListener && document.addEventListener('mousemove',
    function(e) {
      var t = finditem(e,tips);
      if(t || tooltipOn)
        tooltip(e,texttt,t,t);
    },false);
}\n
JAVASCRIPT;
      break;
    case 'fadeEvent' :
      $this->AddFunction('getE');
      $this->AddFunction('init');
      $this->InsertVariable('initfns', NULL, 'fade');
      $fn = <<<JAVASCRIPT
function fade() {
  var f,f1,e,o;
  for(f in fades) {
    f1 = fades[f];
    if(f1.dir) {
      e = getE(f1.id);
      o = (e.style.opacity || fstart) * 1 + f1.dir;
      e.style.opacity = o < .01 ? 0 : (o > .99 ? 1 : o);
    }
  }
  setTimeout(fade,50);
}\n
JAVASCRIPT;
      break;
    case 'fadeEventIn' :
      $this->AddFunction('init');
      $this->AddFunction('finditem');
      $this->InsertVariable('initfns', NULL, 'fiEvt');
      $fn = <<<JAVASCRIPT
function fiEvt() {
  var f;
  for(f in fades)
    getE(fades[f].id).style.opacity = fstart;
  document.addEventListener && document.addEventListener('mouseover',
    function(e) {
      var t = finditem(e,fades);
      t && (t.dir = fistep);
    },false);
}\n
JAVASCRIPT;
      break;
    case 'fadeEventOut' :
      $this->AddFunction('init');
      $this->AddFunction('finditem');
      $this->InsertVariable('initfns', NULL, 'foEvt');
      $fn = <<<JAVASCRIPT
function foEvt() {
  document.addEventListener && document.addEventListener('mouseout',
    function(e) {
      var t = finditem(e,fades);
      t && (t.dir = fostep);
    },false);
}\n
JAVASCRIPT;
      break;
    case 'duplicate' :
      $this->AddFunction('getE');
      $this->AddFunction('newel');
      $this->AddFunction('init');
      $this->InsertVariable('initfns', NULL, 'initDups');
      $fn = <<<JAVASCRIPT
function duplicate(f,t) {
  var e = getE(f), g, a, p = e && e.parentNode;
  if(e) {
    while(p.parentNode && p.tagName != 'svg' &&
      (p.tagName != 'g' || !p.getAttributeNS(null,'clip-path'))) {
      p.tagName == 'a' && (a = p);
      p = p.parentNode;
    }
    g = e.cloneNode(true);
    g.style.opacity = 0;
    e.id = t;

    if(a) {
      a = a.cloneNode(false);
      a.appendChild(g);
      g = a;
    }
    p.appendChild(g);
  }
}
function initDups() {
  for(var d in dups)
    duplicate(d,dups[d]);
}\n
JAVASCRIPT;
      break;
    case 'svgCoords' :
      $fn = <<<JAVASCRIPT
function svgCoords(e) {
  var d = e.target.correspondingUseElement || e.target, m;
  while(d.parentNode && d.nodeName != 'svg')
    d = d.parentNode;
  m = d.getScreenCTM ? d.getScreenCTM() : {e:0,f:0};
  return [m.e,m.f];
}\n
JAVASCRIPT;
      break;
    case 'autoHide' :
      $this->AddFunction('init');
      $this->AddFunction('getE');
      $this->AddFunction('setattr');
      $this->InsertVariable('initfns', NULL, 'autoHide');
      $fn = <<<JAVASCRIPT
function autoHide() {
  if(document.addEventListener) {
    for(var a in autohide)
      autohide[a] = getE(a);
    document.addEventListener('mouseout', function(e) {
      setattr(finditem(e,autohide),'opacity',1);
    });
    document.addEventListener('mouseover', function(e) {
      setattr(finditem(e,autohide),'opacity',0);
    });
  }
}\n
JAVASCRIPT;
      break;
    case 'dragOver' :
      $this->AddFunction('getE');
      $this->AddFunction('setattr');
      $fn = <<<JAVASCRIPT
function dragOver(e,el) {
  var t = getE(el), d, bb;
  if(t && t.dragging) {
    d = t.draginfo;
    bb = t.getBBox();
    d[2] = e.clientX - d[0] - (bb ? bb.width / 2 : 10);
    d[3] = e.clientY - d[1] - (bb ? bb.height / 2 : 10);
    setattr(d[4], 'transform', 'translate(' + d[2] + ',' + d[3] + ')');
    return false;
  }
}\n
JAVASCRIPT;
      break;
    case 'dragStart' :
      $this->AddFunction('getE');
      $this->AddFunction('newel');
      $fn = <<<JAVASCRIPT
function dragStart(e,el) {
  var t = getE(el), m;
  if(!t.draginfo) {
    t.draginfo = [e.clientX,e.clientY,0,0,newel('g',{cursor:'move'})];
    t.parentNode.appendChild(t.draginfo[4]);
    t.parentNode.removeChild(t);
    t.draginfo[4].appendChild(t);
  }
  m = t.getScreenCTM();
  t.draginfo[0] = m.e - t.draginfo[2];
  t.draginfo[1] = m.f - t.draginfo[3];
  t.dragging = 1;
  return false;
}\n
JAVASCRIPT;
      break;
    case 'dragEnd' :
      $this->AddFunction('getE');
      $fn = <<<JAVASCRIPT
function dragEnd(e,el) {
  getE(el).dragging = null;
}\n
JAVASCRIPT;
      break;
    case 'dragEvent' :
      $this->AddFunction('init');
      $this->AddFunction('newel');
      $this->AddFunction('getE');
      $this->AddFunction('setattr');
      $this->AddFunction('finditem');
      $this->InsertVariable('initfns', NULL, 'initDrag');
      $fn = <<<JAVASCRIPT
function initDrag() {
  var d, e;
  if(document.addEventListener) {
    for(d in draggable) {
      e = draggable[d] = getE(d);
      e.draginfo = [0,0,0,0,newel('g',{cursor:'move'})];
      e.parentNode.appendChild(e.draginfo[4]);
      e.parentNode.removeChild(e);
      e.draginfo[4].appendChild(e);
    }
    document.addEventListener('mouseup', function(e) {
      var t = finditem(e,draggable);
      if(t && t.dragging) {
        t.dragging = null;
      }
    });
    document.addEventListener('mousedown', function(e) {
      var t = finditem(e,draggable), m, d;
      if(t && !t.dragging) {
        t.dragging = 1;
        m = t.getScreenCTM();
        d = t.draginfo;
        d[0] = m.e - d[2];
        d[1] = m.f - d[3];
        return false;
      }
    });
    function dragmove(e) {
      var t = finditem(e,draggable), d, bb;
      if(t && t.dragging) {
        d = t.draginfo;
        bb = t.getBBox();
        d[2] = e.clientX - d[0] - (bb ? bb.width / 2 : 10);
        d[3] = e.clientY - d[1] - (bb ? bb.height / 2 : 10);
        setattr(d[4], 'transform', 'translate(' + d[2] + ',' + d[3] + ')');
        return false;
      }
    };
    document.addEventListener('mousemove', dragmove);
    document.addEventListener('mouseout', dragmove);
  }
}\n
JAVASCRIPT;
      break;
    case 'init' :
      $this->onload = TRUE;
      $fn = <<<JAVASCRIPT
function init() {
  if(!document.addEventListener || !initfns)
    return;
  for(var f in initfns)
    eval(initfns[f] + '()');
}\n
JAVASCRIPT;
      break;

    default :
      // Trying to add a function that doesn't exist?
      throw new Exception("Unknown function '$name'");
    }

    $this->InsertFunction($name, $fn);
  }

  /**
   * Inserts a Javascript function into the list
   */
  public function InsertFunction($name, $fn)
  {
    $this->functions[$name] = $fn;
  }

  /**
   * Adds a Javascript variable
   * - use $value:$more for assoc
   * - use NULL:$more for array
   */
  public function InsertVariable($var, $value, $more = NULL, $quote = TRUE)
  {
    $q = $quote ? "'" : '';
    if(is_null($more))
      $this->variables[$var] = $q . $value . $q;
    elseif(is_null($value))
      $this->variables[$var][] = $q . $more . $q;
    else
      $this->variables[$var][$value] = $q . $more . $q;
  }

  /**
   * Insert a comment into the Javascript section - handy for debugging!
   */
  public function InsertComment($details)
  {
    $this->comments[] = $details;
  }

  /**
   * Adds an inline event handler to an element's array
   */
  public function AddEventHandler(&$array, $evt, $code)
  {
    if(isset($array[$evt]))
      $array[$evt] .= ';' . $code;
    else
      $array[$evt] = $code;
  }

  /**
   * Sets the tooltip for an element
   */
  public function SetTooltip(&$element, $text, $duplicate = FALSE)
  {
    $this->AddFunction('tooltip');
    $this->AddFunction('texttt');
    if($this->compat_events) {
      $this->AddEventHandler($element, 'onmousemove',
        "tooltip(evt,texttt,true,'$text')");
      $this->AddEventHandler($element, 'onmouseout',
        "tooltip(evt,texttt,false,'')");
    } else {
      if(!isset($element['id']))
        $element['id'] = $this->graph->NewID();
      $this->AddFunction('ttEvent');
      $this->InsertVariable('tips', $element['id'], $text);
    }
    if($duplicate) {
      if(!isset($element['id']))
        $element['id'] = $this->graph->NewID();
      $this->AddOverlay($element['id'], $this->graph->NewID());
    }
  }

  /**
   * Sets the fader for an element
   */
  public function SetFader(&$element, $in, $out, $target = NULL,
    $duplicate = FALSE)
  {
    if(!isset($element['id']))
      $element['id'] = $this->graph->NewID();
    if(is_null($target))
      $target = $element['id'];
    $id = $duplicate ? $this->graph->NewID() : $element['id'];
    if($this->compat_events) {
      if($in) {
        $this->AddFunction('fadeIn');
        $this->AddEventHandler($element, 'onmouseover',
          'fadeIn(evt,"' . $target . '", ' . $in . ')');
      }
      if($out) {
        $this->AddFunction('fadeOut');
        $this->AddEventHandler($element, 'onmouseout',
          'fadeOut(evt,"' . $target . '", ' . $out . ')');
      }
    } else {

      $this->AddFunction('fadeEvent');
      if($in) {
        $this->AddFunction('fadeEventIn');
        $this->InsertVariable('fistep', $in, NULL, FALSE);
      }
      if($out) {
        $this->AddFunction('fadeEventOut');
        $this->InsertVariable('fostep', -$out, NULL, FALSE);
      }
      $this->InsertVariable('fades', $element['id'],
        "{id:'{$target}',dir:0}", FALSE);
      $this->InsertVariable('fstart', $in ? 0 : 1, NULL, FALSE);
    }
    if($duplicate)
      $this->AddOverlay($element['id'], $id);
  }

  /**
   * Makes an item draggable
   */
  public function SetDraggable(&$element)
  {
    if(!isset($element['id']))
      $element['id'] = $this->graph->NewID();
    if($this->compat_events) {
      $this->AddFunction('dragOver');
      $this->AddFunction('dragStart');
      $this->AddFunction('dragEnd');
      $this->AddEventHandler($element, 'onmousemove',
        "dragOver(evt,'$element[id]')");
      $this->AddEventHandler($element, 'onmousedown',
        "dragStart(evt,'$element[id]')");
      $this->AddEventHandler($element, 'onmouseup',
        "dragEnd(evt,'$element[id]')");
    } else {
      $this->AddFunction('dragEvent');
      $this->InsertVariable('draggable', $element['id'], 0);
    }
  }

  /**
   * Makes something auto-hide
   */
  public function AutoHide(&$element)
  {
    if(!isset($element['id']))
      $element['id'] = $this->graph->NewID();
    if($this->compat_events) {
      $this->AddFunction('setattr');
      $this->AddFunction('getE');
      $this->AddEventHandler($element, 'onmouseover',
        "setattr(getE('$element[id]'),'opacity',0)");
      $this->AddEventHandler($element, 'onmouseout',
        "setattr(getE('$element[id]'),'opacity',1)");
    } else {
      $this->AddFunction('autoHide');
      $this->InsertVariable('autohide', $element['id'], 0);
    }
  }

  /**
   * Add an overlaid copy of an element, with opacity of 0
   */
  public function AddOverlay($from, $to)
  {
    $this->AddFunction('duplicate');
    $this->InsertVariable('dups', $from, $to);
  }

  /**
   * Returns the variables (and comments) as Javascript code
   */
  public function GetVariables()
  {
    $variables = '';
    if(count($this->variables)) {
      $vlist = array();
      foreach($this->variables as $name => $value) {
        $var = $name;
        if(is_array($value)) {
          if(isset($value[0]) && isset($value[count($value)-1])) {
            $var .= '=[' . implode(',', $value) . ']';
          } else {
            $vs = array();
            foreach($value as $k => $v)
              if($k)
                $vs[] = "$k:$v";

            $var .= '={' . implode(',', $vs) . '}';
          }
        } elseif(!is_null($value)) {
          $var .= "=$value";
        }
        $vlist[] = $var;
      }
      $variables = "var " . implode(', ', $vlist) . ";";
    }
    // comments can be stuck with the variables
    if(count($this->comments)) {
      foreach($this->comments as $c) {
        if(!is_string($c))
          $c = print_r($c, TRUE);
        $variables .= "\n// " . str_replace("\n", "\n// ", $c);
      }
    }
    return $variables;
  }


  /**
   * Returns the functions as Javascript code
   */
  public function GetFunctions()
  {
    $functions = '';
    if(count($this->functions))
      $functions = implode('', $this->functions);
    return $functions;
  }

  /**
   * Returns the onload code to use for the SVG
   */
  public function GetOnload()
  {
    return $this->onload ? 'init()' : '';
  }

}

