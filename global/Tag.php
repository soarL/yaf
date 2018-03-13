<?php
class Tag {
	private $fullTags = [
		'div', 'li', 'button', 'ul', 'form', 'textarea', 'a'
	];
	private $singleTags = [
		 'input', 'img',
	];
	private $type = 'full';
	private $tag = 'div';

	public $attributes = [];
	public $content = '';

	public function __construct($tag) {
		if(in_array($tag, $this->singleTags)) {
			$this->type = 'single';
			$this->tag = $tag;
		}else {
			$this->type = 'full';
			$this->tag = $tag;
		}
	}

	public function addClass($name) {
		$classes = [];
		if(isset($this->attributes['class'])) {
			$classes = $this->attributes['class'];
		}
		if(is_string($name)) {
			$classes[] = $name;
		} else if(is_array($name)) {
			foreach ($name as $val) {
				$classes[] = $val;
			}
		} else {
			throw new Exception('参数类型错误');
		}
		$this->attributes['class'] = $classes;
		return $this;
	}

	public function removeClass($name) {
		$classes = '';
		if(isset($this->attributes['class'])) {
			$classes = $this->attributes['class'];
		}
		if(is_string($name)) {
			if(is_array($classes)) {
				foreach ($classes as $key => $class) {
					if($class==$name) {
						unset($classes[$key]);
					}
				}
			} else {
				if($classes==$name) {
					$classes = '';
				}
			}
		} else if(is_array($name))  {
			foreach ($name as $val) {
				if(is_array($classes)) {
					foreach ($classes as $key => $class) {
						if($class==$val) {
							unset($classes[$key]);
						}
					}
				} else {
					if($classes==$val) {
						$classes = '';
					}
				}
			}
		} else {
			throw new Exception('参数类型错误');
		}
		$this->attributes['class'] = $classes;
		return $this;
	}

	public function hasClass($name) {
		if(isset($this->attributes['class'])) {
			if(is_array($this->attributes['class'])) {
				if(in_array($name, $this->attributes['class'])) {
					return true;
				} else {
					return false;
				}
			} else {
				return $this->attributes['class']==$name?true:false;
			}
		}
		return false;
	}

	public function getAttribute($name) {
		return isset($this->attributes[$name])?$this->attributes[$name]:null;
	}

	public function setAttribute($name, $value) {
		$this->attributes[$name] = $value;
		return $this;
	}

	public function removeAttribute($name) {
		if(isset($this->attributes[$name])) {
			unset($this->attributes[$name]);
		}
		return $this;
	}

	public function setContent($content) {
		$this->content = $content;
		return $this;
	}

	public function getContent() {
		return $this->content;
	}

	public function html() {
		$tagStr = '';
		$tagBegin = '<' . $this->tag;
		$tagEnd = '</'. $this->tag . '>';
		foreach ($this->attributes as $key => $value) {
			if(is_array($value)) {
				$attributeVal = '';
				foreach ($value as $val) {
					$attributeVal .= $val . ' ';
				}
				$tagBegin .= ' ' . $key . '="' . rtrim($attributeVal) . '"';
			} else {
				$tagBegin .= ' ' . $key . '="' . $value . '"';
			}
		}
		if($this->type=='full') {
			$tagBegin = $tagBegin . '>';
			$tagStr = $tagBegin . $this->content . $tagEnd;
		} else {
			$tagBegin = $tagBegin . '/>';
			$tagStr = $tagBegin;
		}
		return $tagStr;
	}

	function __toString() {
		return $this->html();
	}
}