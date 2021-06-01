<?php
class Footer {
    private $i18n = null;
    private $errors = null;

    function __construct($i18n, $errors) {
        $this->i18n = $i18n;
        $this->errors = $errors;
    }
    
    function getFooter() {
        return $this->errors->getErrorsAndWarnings() . '</div>
                        <div id="footer">
                            <a href="https://www.fsi.uni-tuebingen.de/" style="color: white;">© ' . date("Y") . ' ' . $this->i18n->get('studentUnionTuebingen') . '</a>
                            <div> | </div>
                            <div>' . $this->i18n->get('questionsPleaseTo') . ': rri<img src="static/img/atSymbol.png" alt="at symbol" style="vertical-align: middle;">fsi.uni-tuebingen.de</div>
                            <div> | </div>
                            <div>' . $this->i18n->get('madeWith') . '&nbsp;<img src="static/img/love.png" alt="love" style="vertical-align: middle;">&nbsp;' . $this->i18n->get('byFsi') . '</div>
                        </div>
                    </div>
                </body>
            </html>';
    }
}
?>
