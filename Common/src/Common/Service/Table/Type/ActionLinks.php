<?php

namespace Common\Service\Table\Type;

/**
 * ActionLinks type
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ActionLinks extends Selector
{
    const DEFAULT_INPUT_NAME = 'table[action][delete][%d]';

    /**
     * Render
     *
     * @param array $data
     * @param array $column
     * @param string $formattedContent
     *
     * @return string
     */
    public function render($data, $column, $formattedContent = null)
    {
        $translator = $this->getTable()->getServiceLocator()->get('translator');
        $remove = $translator->translate('action_links.remove');
        $replace = $translator->translate('action_links.replace');

        $content = '';

        $content .= $this->renderRemoveLink($data, $column, $remove);
        $content .= $this->renderReplaceLink($data, $column, $replace);

        return $content;
    }

    /**
     * Get input name
     *
     * @param array $column
     * @param string $setting
     *
     * @return string
     */
    private function getInputName($column, $setting)
    {
        if (isset($column[$setting])) {
            return $column[$setting];
        }

        return self::DEFAULT_INPUT_NAME;
    }

    /**
     * Is the link visible
     *
     * @param array $data
     * @param array $column
     * @param string $link
     *
     * @return bool
     */
    private function isLinkVisible($data, $column, $link, $default = true)
    {
        $setting = 'is' . $link . 'Visible';
        if (isset($column[$setting]) && is_callable($column[$setting])) {
            return $column[$setting]($data);
        }

        return $default;
    }

    /**
     * Render remove links
     *
     * @param array $data
     * @param array $column
     * @param string $remove
     *
     * @return string
     */
    private function renderRemoveLink($data, $column, $remove)
    {
        $content = '';
        if ($this->isLinkVisible($data, $column, 'Remove')) {
            $inputName = sprintf($this->getInputName($column, 'deleteInputName'), $data['id']);
            $content .= sprintf(
                '<input type="submit" class="right-aligned trigger-modal" name="%s" value="' .$remove . '">',
                $inputName
            );
        }
        return $content;
    }

    /**
     * Render replace links
     *
     * @param array $data
     * @param array $column
     * @param string $remove
     *
     * @return string
     */
    private function renderReplaceLink($data, $column, $replace)
    {
        $content = '';
        if ($this->isLinkVisible($data, $column, 'Replace', false)) {
            $inputName = sprintf($this->getInputName($column, 'replaceInputName'), $data['id']);
            $content .= sprintf(
                ' <input type="submit" class="right-aligned" name="%s" value="' . $replace . '">', $inputName
            );
        }
        return $content;
    }
}
