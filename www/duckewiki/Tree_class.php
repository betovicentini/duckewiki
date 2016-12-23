<?

  class Tree
  {
    protected $info     = null;
    protected $label    = null;
    protected $parent   = null;
    protected $children = null;

    static $count = 0;

    public function __construct( $label=null, Tree $parent=null )
    {
      $this->setLabel($label);
      if ( $parent !== null )
        $parent->addChild($this);
      $this->children = array();
      ++ Tree::$count;
    }

    private function __clone()
    {
      foreach ( $this->children as $n => $child )
        $this->children[$n] = clone $child;
    }

    public function setLabel( $label='' )
    {
      if ( is_string($label) )
        $this->label = $label;
    }

    public function setInfo( $info='' )
    {
      $this->info = $info;
    }

    public function getLabel()
    {
      return $this->label;
    }

    public function getInfo()
    {
      return $this->info;
    }

    public function isRoot()
    {
      return ( $this->parent === null );
    }

    public function addChild( Tree $child )
    {
      $this->children[] = $child;
      $child->parent = $this;
    }

    public function addChildren( array& $children )
    {
      foreach ( $children as &$child )
        if ( $child instanceof Tree )
          $this->addChild( $child );
    }

    public function childrenCount()
    {
      return count($this->children);
    }

    public function hasChildren()
    {
      return ( $this->childrenCount() > 0 );
    }

    public function isLeaf()
    {
      return !$this->hasChildren();
    }

    public function toNewick( $level=0 )
    {
      $endl = "\n";
      $indent_sign = "  ";
      $result = "";

      $indent = "";
      for ( $i=0; $i<$level; $i++ )
        $indent .= $indent_sign;

      if ( count($this->children) )
      {
        $result .= "(";
        $first_child = true;
        foreach ( $this->children as $child )
        {
          if ( !$first_child )
            $result .= ",";
          $result .= "$endl$indent$indent_sign";
          $result .= $child->toNewick( $level+1 );
          $first_child = false;
        }
        $result .= "$endl$indent)";
      }
      $result .= $this->label;
      if ( $level == 0 )
        $result .= ";$endl";

      return $result;
    }

    public function toNewickCompact( $root=true )
    {
      $result = "";

      if ( count($this->children) )
      {
        $result .= "(";
        $first_child = true;
        foreach ( $this->children as &$child )
        {
          if ( !$first_child )
            $result .= ",";
          $result .= $child->toNewickCompact(false);
          $first_child = false;
        }
        $result .= ")";
      }
      $result .= $this->label;
      if ( $root )
        $result .= ";";

      return $result;
    }

    public function removeTransitNodes()
    {

      // recursively call the method for all the children 
      foreach ( $this->children as &$child )
        $child->removeTransitNodes();

      // replacing every "transit" child with it's only child
      foreach ( $this->children as $n => &$child )
        if ( $child->childrenCount() == 1 ) // $child is a "transit" node
        {
          $child = array_pop($child->children); // replacing it with it's only child
          $child->setParent($this); // correcting new child's parrent link
          // it's important to "pop" the "only child" from the old child's children,
          // otherwice it will be automatically destroyed when the old child dies
        }

      // checking if the root is "transit" (has only one child)
      if ( $this->isRoot() and $this->childrenCount() == 1 )
      {
        $child = array_pop($this->children);
        $child->forgetParent();
        return $child;
      }

      return $this;

    }

    protected function setParent( Tree $parent )
    {
      $this->parent = $parent;
    }

    protected function forgetParent()
    {
      $this->parent = null;
    }

    function destroy()
    {
      $this->parrent = null;
      foreach ( $this->children as $child )
        $child->destroy();
      $this->children = array();
    }

    function __destruct()
    {
      $this->destroy();
      -- Tree::$count;
    }

  }

?>
