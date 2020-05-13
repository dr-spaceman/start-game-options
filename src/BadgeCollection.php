<?php

namespace Vgsite;

class BadgeCollection extends Collection
{
    protected function targetClass()
    {
        return Badge::class;
    }

    public function getEarned(): ?Collection
    {
        $query = "SELECT * FROM badges_earned LEFT JOIN badges USING (bid) WHERE usrid = '".mysqli_real_escape_string($GLOBALS['db']['link'], $usrid)."' ORDER BY datetime";
        $res   = mysqli_query($GLOBALS['db']['link'], $query);
        if(!mysqli_num_rows($res)) return false;
        while($row = mysqli_fetch_assoc($res)) $rows[] = $row;
        
        return $rows;
        
    }
    
    public function renderCollection(): string
    {
        if (!$rows = $this->getEarned()) {
            return '<span class="none">'.$usrname.' hasn\'t earned any badges yet.</span>';
        }

        $ret = '
        <ul class="badges">
            ';
            foreach($rows as $row){
                $ret.= '<li><a href="/~'.$usrname.'/badges/'.$row['bid'].'/'.formatNameURL($row['name']).'" class="badge user-profile-nav"><img src="/bin/img/badges/'.$row['bid'].'.png" width="70" height="70" border="0" title="'.htmlSC($row['name']).'"/></a></li>';
            }
            $ret.= '
        </ul>
        <br style="clear:left;"/>
        ';
        
        return $ret;
        
    }
}
